<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\RoleUtils;
use core\SessionUtils;
use core\Validator;

// TODO: dates provided cannot be the same
//       fromDate have to be earlier date than toDate
//       set minimal value of dates

class EntryController {

    private $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    private $monthsPl = ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień",
        "Październik", "Listopad", "Grudzień"];

    private $place;
    private $fromDate;
    private $fromHour;
    private $fromMinute;
    private $toDate;
    private $toHour;
    private $toMinute;
    private $wasDriver;
    private $subAllowance;
    private $dayOff;
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
            $this->place = ParamUtils::getFromPost("place");
            $this->fromDate = ParamUtils::getFromPost("date_from");
            $this->toDate = ParamUtils::getFromPost("date_to");
            $this->wasDriver = ParamUtils::getFromPost("driver") == "true";
            $this->subAllowance = ParamUtils::getFromPost("subsistence_allowance") == "true";
            $this->dayOff = ParamUtils::getFromPost("day_off") == "true";
            $this->fromHour = intval(ParamUtils::getFromPost("time_from_hour"));
            $this->fromMinute = intval(ParamUtils::getFromPost("time_from_minute"));
            $this->toHour = intval(ParamUtils::getFromPost("time_to_hour"));
            $this->toMinute = intval(ParamUtils::getFromPost("time_to_minute"));

            // validate parameters
            $validationResult = $this->validateEntryData(
                $this->place,
                $this->fromDate,
                $this->fromHour,
                $this->fromMinute,
                $this->toDate,
                $this->toHour,
                $this->toMinute,
                $this->dayOff
                );

            if ($validationResult) {
                if ($this->dayOff) {
                    $this->addDayOffEntry($this->fromDate);
                } else {

                    $fromTime = $this->formatDateAndTime($this->fromDate, $this->fromHour, $this->fromMinute);
                    $toTime = $this->formatDateAndTime($this->toDate, $this->toHour, $this->toMinute);
                    $hours = $this->countHoursDifference($fromTime, $toTime);

                    $this->addEntry($this->place, $fromTime, $toTime, $hours, $this->wasDriver,
                        $this->subAllowance, 0);
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
                $wasDriver = ParamUtils::getFromPost("driver") == "true";
                $subAllowance = ParamUtils::getFromPost("subsistence_allowance") == "true";
                $dayOff = ParamUtils::getFromPost("day_off") == "true";
                $fromHour = intval(ParamUtils::getFromPost("time_from_hour"));
                $fromMinute = intval(ParamUtils::getFromPost("time_from_minute"));
                $toHour = intval(ParamUtils::getFromPost("time_to_hour"));
                $toMinute = intval(ParamUtils::getFromPost("time_to_minute"));
                $validationResult = $this->validateEntryData(
                    $place,
                    $fromDate,
                    $fromHour,
                    $fromMinute,
                    $toDate,
                    $toHour,
                    $toMinute,
                    $dayOff
                );
                if ($validationResult) {
                    $fromTime = $this->formatDateAndTime($this->fromDate, $this->fromHour, $this->fromMinute);
                    $toTime = $this->formatDateAndTime($this->toDate, $this->toHour, $this->toMinute);
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
            App::getMessages()->addMessage(new Message("Wpis dla dnia " . $fromDate . " już istnieje. 
            Aby wprowadzić nowy wpis dla danego dnia edytuj istniejący, lub usuń go i dodaj nowy.", Message::INFO));
        } else {
            App::getDB()->insert("work_hour_entry", [
                "uuid"=>generate_uuid(),
                "from_date"=> $fromDate,
                "to_date"=> $toDate,
                "hours"=> $hours,
                "place"=>$place,
                "was_driver"=>$wasDriver ? 1 : 0,
                "subsistence_allowance"=>$subAllowance ? 1 : 0,
                "year"=>$yearAndMonth[0],
                "month"=>$yearAndMonth[1],
                "day_off"=>$dayOff ? 1 : 0,
                "user_uuid"=>SessionUtils::load("userUuid", true)
            ]);
            App::getMessages()->addMessage(new Message("Pomyślnie dodano wpis dla dnia $this->fromDate", Message::INFO));
        }
    }

    private function addDayOffEntry($fromDate) {
        $this->addEntry(null, $fromDate, null, null, null, true, 1);
    }

    private function editEntry($entryUuid, $place, $fromDate, $toDate, $hours, $wasDriver, $subAllowance, $dayOff) {
        // TODO fix date saving - its 1970-01-01
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

    private function validateEntryData(&$place, &$fromDate, &$fromHour, &$fromMinute, &$toDate, &$toHour, &$toMinute, &$dayOff) {
        $paramRequired = true;
        $v = new Validator();
        // if day is off validate date only, and return result
        if ($dayOff) {
            $paramRequired = false;
        }

        // TODO assign $fromDate = $v->validate (same for toDate) and refactor function handling date operations
        // to use DateTime class and OOP approach

        // TODO fix problem when hour/minute is not passed, than it is validated correctly

        $v->validate($fromDate, [
            "required"=>"true",
            "required_message"=>'"Data od" jest wymagana przy wprowadzaniu dnia wolnego',
            "trim"=>"true",
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'Niepoprawny format "Data od" (wymagany: YYYY-mm-dd)'
        ]);

        $v->validate($toDate, [
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

        $fromHour = $v->validate($fromHour, [
           "required"=>$paramRequired,
           "required_message"=>'"Godzina od" jest wymagana przy wprowadzaniu dnia pracującego',
           "int"=>"true",
           "min"=> 00,
           "max"=>23,
           "validator_message"=>'"Godzina od" musi być w zakresie od 00 do 23'
        ]);

        $toHour = $v->validate($toHour, [
            "required"=>$paramRequired,
            "required_message"=>'"Godzina do" jest wymagana przy wprowadzaniu dnia pracującego',
            "int"=>"true",
            "min"=> 00,
            "max"=>23,
            "validator_message"=>'"Godzina do" musi być w zakresie od 00 do 23'
        ]);

        $fromMinute = $v->validate($fromMinute, [
            "required"=>$paramRequired,
            "required_message"=>'"Minuta od" jest wymagana przy wprowadzaniu dnia pracującego',
            "int"=>"true",
            "min"=> 0,
            "max"=>59,
            "validator_message"=>'"Minuta od" musi być w zakresie od 00 do 59'
        ]);

        $toMinute = $v->validate($toMinute, [
            "required"=>$paramRequired,
            "required_message"=>'"Minuta do" jest wymagana przy wprowadzaniu dnia pracującego',
            "int"=>"true",
            "min"=> 0,
            "max"=>59,
            "validator_message"=>'"Minuta do" musi być w zakresie od 00 do 59'
        ]);

        return !App::getMessages()->isError();
    }

    private function checkAnotherEntryForSameDay($fromDate) {
        $date = date("Y-m-d",strtotime($fromDate));
        return App::getDB()->count("work_hour_entry", [
            "from_date[~]"=>$date . "%",
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function formatDateAndTime($date, $hours, $minutes) {
        $dateWithTime = "$date $hours:$minutes";
        return date("Y-m-d H:i:s", strtotime($dateWithTime));
    }

    private function countHoursDifference($fromTime, $toTime) {
        $from = strtotime($fromTime);
        $to = strtotime($toTime);
        // subtract dates and divide by 3600 to get difference in hours
        return ($to - $from) / 3600;
    }

    private function getCurrentMonthPl() {
        return $this->monthsPl[array_search($this->currentMonth, $this->months)];
    }

    private function getYearAndMonth($fromDate) {
        $year = date("Y", strtotime($fromDate));
        $monthIdx = intval(date("m", strtotime($fromDate)));
        $month = $this->months[$monthIdx - 1];
        return array($year, $month);
    }

    private function renderEntriesTable() {
        App::getSmarty()->assign("description", "Godziny w bieżącym miesiącu (" .
            $this->getCurrentMonthPl() . " $this->currentYear)");
        App::getSmarty()->assign("entries", $this->getEntries($this->currentYear, $this->currentMonth));
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


}
