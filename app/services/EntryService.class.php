<?php

namespace app\services;

use core\App;
use core\Message;
use core\SessionUtils;
use core\Validator;
use function mysql_xdevapi\getSession;

class EntryService {

    private $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    private $monthsPl = ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień",
        "Październik", "Listopad", "Grudzień"];

    private $currentYear;
    private $currentMonth;

    function __construct() {
        $this->currentYear = date("Y");
        $this->currentMonth = date("M");
    }

    public function getEntries($year=null, $month=null, $size=10, $page=1) {
        $limitDown = $size * ($page - 1);
        $limitUp = $size * $page;
        $entries = array();
        // if year and month parameters are provided get entries from exact month and year
        // else get entries from current month and year
        if(isset($year) && is_numeric($year) && isset($month) && in_array($month, $this->months)) {
            $entries = App::getDB()->select("work_hour_entry", "*", [
                "month"=>$month,
                "year"=>$year,
                "user_uuid"=>SessionUtils::load("userUuid", true),
                "ORDER"=>"from_date",
                "LIMIT"=>[$limitDown, $limitUp]
            ]);
        } else {
            $entries = App::getDB()->select("work_hour_entry", "*", [
                "user_uuid"=>SessionUtils::load("userUuid", true),
                "ORDER"=>"from_date",
                "LIMIT"=>[$limitDown, $limitUp]
            ]);
        }
        return $entries;
    }

    private function getEntriesCount($year=null, $month=null, $size=10, $page=1) {
        $limitDown = $size * ($page - 1);
        $limitUp = $size * $page;
        $entries = 0;
        if(isset($year) && is_numeric($year) && isset($month) && in_array($month, $this->months)) {
            $entries = App::getDB()->count("work_hour_entry", "*", [
                "month"=>$month,
                "year"=>$year,
                "user_uuid"=>SessionUtils::load("userUuid", true),
                "ORDER"=>"from_date",
                "LIMIT"=>[$limitDown, $limitUp]
            ]);
        } else {
            $entries = App::getDB()->count("work_hour_entry", "*", [
                "user_uuid"=>SessionUtils::load("userUuid", true),
                "ORDER"=>"from_date",
                "LIMIT"=>[$limitDown, $limitUp]
            ]);
        }
        $pages = ceil($entries / $size);
        return $pages;
    }

    private function getEntry($entryUuid) {
        $data = App::getDB()->select("work_hour_entry", "*", [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data[0];
    }

    public function addEntry($place, $fromDate, $toDate, $hours, $wasDriver, $subAllowance, $dayOff) {
        // get year and month - function returns array - idx 0 is year, 1 - month
        $yearAndMonth = $this->getYearAndMonth($fromDate);
        if ($this->checkAnotherEntryForSameDay($fromDate)){
            App::getMessages()->addMessage(new Message("Wpis dla dnia " . $fromDate->format("Y-m-d") . " już istnieje. 
            Aby wprowadzić nowy wpis dla danego dnia edytuj istniejący, lub usuń go i dodaj nowy.", Message::INFO));
        } else {
            App::getDB()->insert("work_hour_entry", [
                "uuid"=>generate_uuid(),
                "from_date"=> $fromDate->format("Y-m-d H:i"),
                "to_date"=> $dayOff ? null : $toDate->format("Y-m-d H:i"),
                "hours"=> $hours,
                "place"=>$place,
                "was_driver"=>$wasDriver ? 1 : 0,
                "subsistence_allowance"=>$subAllowance ? 1 : 0,
                "year"=>$yearAndMonth[0],
                "month"=>$yearAndMonth[1],
                "day_off"=>$dayOff ? 1 : 0,
                "user_uuid"=>SessionUtils::load("userUuid", true)
            ]);
            App::getMessages()->addMessage(new Message("Pomyślnie dodano wpis dla dnia " . $fromDate->format("Y-m-d"), Message::INFO));
        }
    }

    public function addDayOffEntry($fromDate) {
        $fromDate->setTime(0, 0);
        $this->addEntry(null, $fromDate, null, null, null, null, 1);
    }

    public function editEntry($entryUuid, $place, $fromDate, $toDate, $hours, $wasDriver, $subAllowance, $dayOff) {
        // get year and month - function returns array - idx 0 is year, 1 - month
        $yearAndMonth = $this->getYearAndMonth($fromDate);
        App::getDB()->update("work_hour_entry", [
            "from_date"=> $fromDate->format("Y-m-d H:i"),
            "to_date"=> $dayOff ? null : $toDate->format("Y-m-d H:i"),
            "hours"=> $hours,
            "place"=>$place,
            "was_driver"=>$wasDriver ? 1 : 0,
            "subsistence_allowance"=>$subAllowance ? 1 : 0,
            "year"=>$yearAndMonth[0],
            "month"=>$yearAndMonth[1],
            "day_off"=>$dayOff ? 1 : 0
        ], [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        App::getMessages()->addMessage(new Message("Pomyślnie edytowano wpis", Message::INFO));
    }

    public function editToDayOffEntry($entryUuid, $fromDate) {
        $fromDate->setTime(0, 0);
        $this->editEntry($entryUuid, null, $fromDate, null, null, null, null, 1);
    }

    public function deleteEntry($entryUuid) {
        $data = App::getDB()->delete("work_hour_entry", [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data->rowCount();
    }

    public function entryExist($entryUuid) {
        $result = App::getDB()->has("work_hour_entry", [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Wpis o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    public function validateEntryData(&$place, &$fromDate, &$toDate, &$fromTime, &$toTime, &$dayOff) {
        $paramRequired = true;
        $v = new Validator();
        // if day is off validate fromDate only, and return result
        if ($dayOff) {
            $paramRequired = false;
        }

        $fromDate = $v->validate($fromDate, [
            "required"=>"true",
            "required_message"=>'"Data od" jest wymagana przy wprowadzaniu dnia wolnego',
            "trim"=>"true",
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'Niepoprawny format "Data od" (wymagany: YYYY-mm-dd)'
        ]);

        $toDate = $v->validate($toDate, [
            "required"=>$paramRequired,
            "required_message"=>'"Data do" jest wymagana przy wprowadzaniu dnia pracującego',
            "trim"=>"true",
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'Niepoprawny format "Data do" (wymagany: YYYY-mm-dd)'
        ]);

        $place = $v->validate($place, [
            "escape"=>"true",
            "trim"=>"true",
            "required"=>$paramRequired,
            "required_message"=>'Miejsce jest wymagane przy wprowadzaniu dnia pracującego',
            "min_length"=>3,
            "max_length"=>90,
            "validator_message"=>"Miejsce musi mieć od 3 do 90 znaków"
        ]);

        $fromTime = $v->validate($fromTime, [
            "required"=>$paramRequired,
            "required_message"=>'"Godzina od" jest wymagana przy wprowadzaniu dnia pracującego',
            "date_format"=>"H:i",
            "validator_message"=>'Niepoprawny format "Godzina od" (wymagany: HH:MM)'
        ]);

        $toTime = $v->validate($toTime, [
            "required"=>$paramRequired,
            "required_message"=>'"Godzina do" jest wymagana przy wprowadzaniu dnia pracującego',
            "date_format"=>"H:i",
            "validator_message"=>'Niepoprawny format "Godzina do" (wymagany: HH:MM)'
        ]);

        if ($paramRequired && !empty($toTime) && !empty($fromTime)) {
            $fromDateWithTime = $fromDate->setTime($fromTime->format("H"), $fromTime->format("i"));
            $toDateWithTime = $toDate->setTime($toTime->format("H"), $toTime->format("i"));
            if ($fromDateWithTime >= $toDateWithTime) {
                App::getMessages()->addMessage(new Message('"Data od" musi być wcześniejsza od "Data do" (wliczając godziny)', Message::ERROR));
            }
        }

        return !App::getMessages()->isError();
    }

    private function checkAnotherEntryForSameDay($fromDate) {
        $date = $fromDate->format("Y-m-d");
        return App::getDB()->count("work_hour_entry", [
            "from_date[~]"=>$date . "%",
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function formatDateAndTime(\DateTime $date, \DateTime $time) {
        $timeArr = explode(":", $time->format("H:i"));
        $date->setTime(intval($timeArr[0]), intval($timeArr[1]));
        return $date;
    }

    public function countHoursDifference(\DateTime $fromTime, \DateTime $toTime) {
        $diff = $fromTime->diff($toTime);
        $hours = $diff->h;
        if ($diff->i == 30) {
            $hours += 0.5;
        }
        return $hours;
    }

    private function getCurrentMonthPl() {
        return $this->monthsPl[array_search($this->currentMonth, $this->months)];
    }

    private function getMonthPl($month) {
        return $this->monthsPl[array_search($month, $this->months)];
    }

    public function getYearAndMonth($fromDate) {
        $year = $fromDate->format("Y");
        $monthIdx = intval($fromDate->format("m"));
        $month = $this->months[$monthIdx - 1];
        return array($year, $month);
    }

    public function getFilteredEntries($place, $hours, $wasDriver, $subAllowance, $dayOff, $filter, $size=10, $page=1) {
        $where = [
            "user_uuid"=>SessionUtils::load("userUuid", true),
            "ORDER"=>[
                "from_date"
        ]
        ];
        if (!empty($place)) {
            $where["place[~]"] = $place;
        }
        if (!empty($hours)) {
            $where["hours"] = $hours;
        }
        if (!is_null($wasDriver)) {
            switch ($wasDriver) {
                case "true":
                    $where["was_driver"] = 1;
                    break;
                case "false":
                    $where["was_driver"] = 0;
                    break;
            }
        }
        if (!is_null($subAllowance)) {
            switch ($subAllowance) {
                case "true":
                    $where["subsistence_allowance"] = 1;
                    break;
                case "false":
                    $where["subsistence_allowance"] = 0;
                    break;
            }
        }
        if (!is_null($dayOff)) {
            switch ($dayOff) {
                case "true":
                    $where["day_off"] = 1;
                    break;
                case "false":
                    $where["day_off"] = 0;
                    break;
            }
        }
        $sizeFrom = 0;
        $sizeTo = $size;
        if ($page > 1) {
            $sizeFrom = ($page - 1) * $size;
            $sizeTo = $page * $size;
        }

        $count = App::getDB()->count("work_hour_entry", "*", $where);
        $where["LIMIT"] = [$sizeFrom, $sizeTo];
        $pages = ceil($count / $size);

        if (($filter == "true") || ($count < $sizeFrom)) {
            $sizeFrom = 0;
            $sizeTo = $size;
            $where["LIMIT"] = [$sizeFrom, $sizeTo];
            $page = 1;
        }

        App::getSmarty()->assign("page", $page);
        App::getSmarty()->assign("pages", $pages);
        return App::getDB()->select("work_hour_entry", "*", $where);
    }

    public function renderEntriesTable() {
        App::getSmarty()->assign("description", "Godziny w bieżącym miesiącu (" .
            $this->getCurrentMonthPl() . " $this->currentYear)");
        App::getSmarty()->assign("pages_count", $this->getEntriesCount($this->currentYear, $this->currentMonth));
        App::getSmarty()->assign("page", 1);
        App::getSmarty()->assign("size", 10);
        App::getSmarty()->assign("entries", $this->getEntries($this->currentYear, $this->currentMonth));
        App::getSmarty()->display("entriesTable.tpl");
    }

    public function renderMonthEntriesTable($year, $month) {
        App::getSmarty()->assign("description", "Godziny w miesiącu " .
            $this->getMonthPl($month) . " $year");
        App::getSmarty()->assign("pages_count", $this->getEntriesCount($year, $month));
        App::getSmarty()->assign("page", 1);
        App::getSmarty()->assign("size", 10);
        App::getSmarty()->assign("entries", $this->getEntries($year, $month));
        App::getSmarty()->display("entriesTable.tpl");
    }

    public function renderAddEntryForm() {
        App::getSmarty()->assign("description", "Dodaj wpis");
        App::getSmarty()->display("addEntryForm.tpl");
    }

    public function renderEditEntryForm($entryUuid) {
        App::getSmarty()->assign("description", "Edytuj wpis");
        App::getSmarty()->assign("entry", $this->getEntry($entryUuid));
        App::getSmarty()->display("editEntryForm.tpl");
    }

    public function renderChooseEntryMonth() {
        App::getSmarty()->assign("description", "Wybierz miesiąc");
        App::getSmarty()->display("chooseEntryMonth.tpl");
    }

    public function renderAjaxEntriesPage($place, $hours, $wasDriver, $subAllowance, $dayOff, $filter, $size, $page) {
        App::getSmarty()->assign("entries", $this->getFilteredEntries($place, $hours, $wasDriver, $subAllowance, $dayOff, $filter, $size, $page));
        App::getSmarty()->display("table.tpl");
    }
}
