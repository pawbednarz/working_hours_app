<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;

class EntryController {

    private $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    private $monthsPl = ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień",
        "Październik", "Listopad", "Grudzień"];

    private $place;
    private $fromDate;
    private $toDate;
    private $wasDriver;
    private $subAllowance;
    private $dayOff;

    public function action_dashboard() {
        // get current month and year
        $currentYear = date("Y");
        $currentMonth = date("M");
        // cast eng month to pl month
        $currentMonthPl = $this->monthsPl[array_search($currentMonth, $this->months)];
        App::getSmarty()->assign("description", "Godziny w bieżącym miesiącu ($currentMonthPl $currentYear)");
        App::getSmarty()->assign("entries", $this->getEntries($currentYear, $currentMonth));
        $this->renderTemplate("dashboard.tpl");
    }

    public function action_showEntries() {
        $this->getEntries();
    }

    public function action_addEntry() {
        // if user clicked the button (GET request) display add entry form
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $this->renderTemplate("addEntry.tpl");
        } else if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // get request parameters
            $this->place = ParamUtils::getFromPost("place");
            $this->fromDate = ParamUtils::getFromPost("date_from");
            $this->toDate = ParamUtils::getFromPost("date_to");
            $this->wasDriver = ParamUtils::getFromPost("driver") == "true";
            $this->subAllowance = ParamUtils::getFromPost("subsistence_allowance") == "true";
            $this->dayOff = ParamUtils::getFromPost("day_off") == "true";

            // validate parameters
            $validationResult = $this->validateEntryData($this->place, $this->fromDate, $this->toDate, $this->dayOff);

            if ($validationResult) {
                if ($this->dayOff) {
                    $this->addDayOffEntry($this->fromDate);
                } else {
                    $this->addEntry($this->place, $this->fromDate, $this->toDate, $this->wasDriver,
                        $this->subAllowance, 0);
                }
            }
            $this->renderTemplate("addEntry.tpl");
        }
    }

    public function action_editEntry() {

    }

    public function action_deleteEntry() {

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
            $entries = App::getDB()->select("work_hour_entry", "*");
        }
        return $entries;
    }

    private function addEntry($place, $fromDate, $toDate, $wasDriver, $subAllowance, $dayOff) {
        $year = date("Y", strtotime($fromDate));
        $monthIdx = intval(date("m", strtotime($fromDate)));
        $month = $this->months[$monthIdx - 1];
        if ($this->checkAnotherEntryForSameDay($fromDate, $year, $month)){
            App::getMessages()->addMessage(new Message("Wpis dla dnia " . $fromDate . " już istnieje. 
            Aby wprowadzić nowy wpis dla danego dnia edytuj istniejący, lub usuń go i dodaj nowy.", Message::INFO));
        } else {
            App::getDB()->insert("work_hour_entry", [
                "uuid"=>generate_uuid(),
                "from_date"=> $fromDate,
                "to_date"=> $toDate,
                // TODO count from hours
                "hours"=> 8,
                "place"=>$place,
                "was_driver"=>$wasDriver ? 1 : 0,
                "subsistence_allowance"=>$subAllowance ? 1 : 0,
                "year"=>$year,
                "month"=>$month,
                "day_off"=>$dayOff ? 1 : 0,
                "user_uuid"=>SessionUtils::load("userUuid", true)
            ]);
            App::getMessages()->addMessage(new Message("Pomyślnie dodano wpis dla dnia $this->fromDate", Message::INFO));
        }
    }

    private function addDayOffEntry($fromDate) {
        $this->addEntry(null, $fromDate, null, null, null, true);
    }

    private function validateEntryData($place, $fromDate, $toDate, $dayOff) {
        $paramRequired = true;
        $v = new Validator();
        // if day is off validate date only, and return result
        if ($dayOff) {
            $paramRequired = false;
        }

        // TODO make some more date validation (to prevent from entering input like 0000-00-00)
        $v->validate($fromDate, [
            "required"=>true,
            "required_message"=>'"Data od" jest wymagana przy wprowadzaniu dnia wolnego',
            "trim"=>true,
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'Niepoprawny format "Data od" (wymagany: YYYY-mm-dd)'
        ]);

        $v->validate($toDate, [
            "required"=>$paramRequired,
            "required_message"=>'"Data do" jest wymagana przy wprowadzaniu dnia pracującego',
            "trim"=>true,
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'Niepoprawny format "Data od" (wymagany: YYYY-mm-dd)'
        ]);

        $v = new Validator();
        $place = $v->validate($place, [
            "escape"=>true,
            "trim"=>true,
            "required"=>$paramRequired,
            "required_message"=>'Miejsce jest wymagane przy wprowadzaniu dnia pracującego',
            "min_length"=>3,
            "max_length"=>90,
            "validator_message"=>"Miejsce musi mieć od 3 do 90 znaków"
        ]);

        return !App::getMessages()->isError();
    }

    private function checkAnotherEntryForSameDay($fromDate, $year, $month) {
        return App::getDB()->count("work_hour_entry", [
            "from_date"=>$fromDate,
            "year"=>$year,
            "month"=>$month,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function renderTemplate($template) {
        App::getSmarty()->display($template);
    }
}
