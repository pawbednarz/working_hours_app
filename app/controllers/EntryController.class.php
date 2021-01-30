<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\RoleUtils;
use core\SessionUtils;
use core\Validator;

class EntryController {

    private $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    private $monthsPl = ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień",
        "Październik", "Listopad", "Grudzień"];

    private $currentYear;
    private $currentMonth;

    function __construct() {
        $this->currentYear = date("Y");
        $this->currentMonth = date("M");
    }

    public function action_dashboard() {
        if (RoleUtils::inRole("admin")) {
            App::getRouter()->redirectTo("adminDashboard");
        }
        $this->renderEntriesTable();
    }

    public function action_showEntries() {
        $this->getEntries();
    }

    public function action_addEntry() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // get request parameters
            $place = ParamUtils::getFromPost("place");
            $fromDate = ParamUtils::getFromPost("date_from");
            $fromTime = ParamUtils::getFromPost("time_from");
            $toDate = ParamUtils::getFromPost("date_to");
            $toTime = ParamUtils::getFromPost("time_to");
            $wasDriver = ParamUtils::getFromPost("driver") == "true";
            $subAllowance = ParamUtils::getFromPost("subsistence_allowance") == "true";
            $dayOff = ParamUtils::getFromPost("day_off") == "true";

            // validate parameters
            $validationResult = $this->validateEntryData($place, $fromDate, $toDate, $fromTime,
                $toTime, $dayOff);

            if ($validationResult) {
                if ($dayOff) {
                    $this->addDayOffEntry($fromDate);
                } else {
                    $fromDateAndTime = $this->formatDateAndTime($fromDate, $fromTime);
                    $toDateAndTime = $this->formatDateAndTime($toDate, $toTime);
                    $hours = $this->countHoursDifference($fromDateAndTime, $toDateAndTime);

                    $this->addEntry($place, $fromDateAndTime, $toDateAndTime, $hours, $wasDriver, $subAllowance, 0);
                }
            }
        }
        $this->renderAddEntryForm();
    }

    public function action_editEntry() {
        $entryUuid = ParamUtils::getFromGet("entry_uuid");
        $v = new Validator();

        if ($v->validateUuid($entryUuid) && $this->entryExist($entryUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $place = ParamUtils::getFromPost("place");
                $fromDate = ParamUtils::getFromPost("date_from");
                $toDate = ParamUtils::getFromPost("date_to");
                $fromTime = intval(ParamUtils::getFromPost("time_to"));
                $toTime = intval(ParamUtils::getFromPost("time_from"));
                $wasDriver = ParamUtils::getFromPost("driver") == "true";
                $subAllowance = ParamUtils::getFromPost("subsistence_allowance") == "true";
                $dayOff = ParamUtils::getFromPost("day_off") == "true";
                $validationResult = $this->validateEntryData($place, $fromDate, $toDate, $fromTime,
                    $toTime, $dayOff);
                if ($validationResult) {
                    $fromTime = $this->formatDateAndTime($fromDate, $fromTime);
                    $toTime = $this->formatDateAndTime($toDate, $toTime);
                    $hours = $this->countHoursDifference($fromTime, $toTime);

                    $this->editEntry($entryUuid, $place, $fromTime, $toTime, $hours, $wasDriver, $subAllowance, $dayOff);
                    App::getMessages()->addMessage(new Message("Pomyślnie edytowano wpis", Message::INFO));
                }
            }
            $this->renderEditEntryForm($entryUuid);
            exit();
        }
        $this->renderEntriesTable();
    }

    public function action_deleteEntry() {
        $entryUuid = ParamUtils::getFromPost("entry_uuid");
        $v = new Validator();

        if ($v->validateUuid($entryUuid)) {
            $result = $this->deleteEntry($entryUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto wpis", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć wpisu", Message::ERROR));
            }
        }
        $this->renderEntriesTable();
    }

    public function action_showEntriesForMonth() {
        $dateFrom = ParamUtils::getFromGet("date_from");
        if (isset($dateFrom) && $dateFrom != "") {
            $v = new Validator();
            $dateFrom = $v->validate($dateFrom, [
                "required"=>"true",
                "required_message"=>'Data jest wymagana przy wyborze miesiąca',
                "date_format"=>"Y-m",
                "validator_message"=>'Niepoprawny format daty (wymagany: YYYY-mm)'
            ]);
            echo $dateFrom->format("Y-m");
            if ($v->isLastOK()) {
                $inputDate = $this->getYearAndMonth($dateFrom);
                $year = $inputDate[0];
                $month = $inputDate[1];
                $this->renderMonthEntriesTable($year, $month);
                exit();
            }
        }
        $this->renderChooseEntryMonth();
    }

    private function getEntries($year=null, $month=null) {
        $entries = array();
        // if year and month parameters are provided get entries from exact month and year
        // else get entries from current month and year
        if(isset($year) && is_numeric($year) && isset($month) && in_array($month, $this->months)) {
            $entries = App::getDB()->select("work_hour_entry", "*", [
                "month"=>$month,
                "year"=>$year,
                "user_uuid"=>SessionUtils::load("userUuid", true),
                "ORDER"=>"from_date"
            ]);
        } else {
            $entries = App::getDB()->select("work_hour_entry", "*", [
                "user_uuid"=>SessionUtils::load("userUuid", true)
            ]);
        }
        return $entries;
    }

    private function getEntry($entryUuid) {
        $data = App::getDB()->select("work_hour_entry", "*", [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data[0];
    }

    private function addEntry($place, $fromDate, $toDate, $hours, $wasDriver, $subAllowance, $dayOff) {
        // get year and month - function returns array - idx 0 is year, 1 - month
        $yearAndMonth = $this->getYearAndMonth($fromDate);
        if ($this->checkAnotherEntryForSameDay($fromDate)){
            App::getMessages()->addMessage(new Message("Wpis dla dnia " . $fromDate->format("Y-m-d") . " już istnieje. 
            Aby wprowadzić nowy wpis dla danego dnia edytuj istniejący, lub usuń go i dodaj nowy.", Message::INFO));
        } else {
            App::getDB()->insert("work_hour_entry", [
                "uuid"=>generate_uuid(),
                "from_date"=> $fromDate->format("Y-m-d H:i"),
                "to_date"=> is_null($toDate) ? null : $toDate->format("Y-m-d H:i"),
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

    private function addDayOffEntry($fromDate) {
        $fromDate->setTime(0, 0);
        $this->addEntry(null, $fromDate, null, null, null, null, 1);
    }

    private function editEntry($entryUuid, $place, $fromDate, $toDate, $hours, $wasDriver, $subAllowance, $dayOff) {
        // get year and month - function returns array - idx 0 is year, 1 - month
        $yearAndMonth = $this->getYearAndMonth($fromDate);
        App::getDB()->update("work_hour_entry", [
            "from_date"=> $fromDate,
            "to_date"=> $toDate,
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
    }

    private function deleteEntry($entryUuid) {
        $data = App::getDB()->delete("work_hour_entry", [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data->rowCount();
    }

    private function entryExist($entryUuid) {
        $result = App::getDB()->has("work_hour_entry", [
            "uuid"=>$entryUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Wpis o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    private function validateEntryData(&$place, &$fromDate, &$toDate, &$fromTime, &$toTime, &$dayOff) {
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

        $fromDateWithTime = $fromDate->setTime($fromTime->format("H"), $fromTime->format("i"));
        $toDateWithTime = $toDate->setTime($toTime->format("H"), $toTime->format("i"));
        if ($fromDateWithTime >= $toDateWithTime) {
            App::getMessages()->addMessage(new Message('"Data od" musi być wcześniejsza od "Data do" (wliczając godziny)', Message::ERROR));
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

    private function formatDateAndTime(\DateTime $date, \DateTime $time) {
        $timeArr = explode(":", $time->format("H:i"));
        $date->setTime(intval($timeArr[0]), intval($timeArr[1]));
        return $date;
    }

    private function countHoursDifference(\DateTime $fromTime, \DateTime $toTime) {
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

    private function getYearAndMonth($fromDate) {
        $year = $fromDate->format("Y");
        $monthIdx = intval($fromDate->format("m"));
        $month = $this->months[$monthIdx - 1];
        return array($year, $month);
    }

    private function renderEntriesTable() {
        App::getSmarty()->assign("description", "Godziny w bieżącym miesiącu (" .
            $this->getCurrentMonthPl() . " $this->currentYear)");
        App::getSmarty()->assign("entries", $this->getEntries($this->currentYear, $this->currentMonth));
        App::getSmarty()->display("entriesTable.tpl");
    }

    private function renderMonthEntriesTable($year, $month) {
        App::getSmarty()->assign("description", "Godziny w miesiącu " .
            $this->getMonthPl($month) . " $year");
        App::getSmarty()->assign("entries", $this->getEntries($year, $month));
        App::getSmarty()->display("entriesTable.tpl");
    }

    private function renderAddEntryForm() {
        App::getSmarty()->assign("description", "Dodaj wpis");
        App::getSmarty()->display("addEntryForm.tpl");
    }

    private function renderEditEntryForm($entryUuid) {
        App::getSmarty()->assign("description", "Edytuj wpis");
        App::getSmarty()->assign("entry", $this->getEntry($entryUuid));
        App::getSmarty()->display("editEntryForm.tpl");
    }

    private function renderChooseEntryMonth() {
        App::getSmarty()->assign("description", "Wybierz miesiąc");
        App::getSmarty()->display("chooseEntryMonth.tpl");
    }


}
