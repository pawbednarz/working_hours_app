<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
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
            $validationResult = $this->validateEntryData($this->place, $this->fromDate, $this->toDate, $this->wasDriver,
                $this->subAllowance, $this->dayOff);

            if ($validationResult) {
                if ($this->dayOff) {
                    $this->addDayOffEntry($this->fromDate);
                }
            }

            App::getMessages()->addMessage(new Message("Pomyślnie dodano wpis dla dnia $this->fromDate", Message::INFO));
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
            "day_off"=>$dayOff ? 1 : 0
        ]);
    }

    private function addDayOffEntry($fromDate) {
        $this->addEntry(null, $fromDate, null, null, null, true);
    }

    private function validateEntryData($place, $fromDate, $toDate, $wasDriver, $subAllowance, $dayOff) {
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
            "date_format"=>"Y-m-d",
            "validator_message"=>'Niepoprawny format "Data od" (wymagany: Y-m-d)'
        ]);

        return $v->isLastOK();

//        $v = new Validator();
//        $place = $v->validate($place, [
//            "escape"=>true,
//            "trim"=>true,
//            "required"=>true,
//            "required_message"=>'Miejsce jest wymagane',
//            "min_length"=>3,
//            "max_length"=>90,
//            "validator_message"=>"Miejsce musi mieć od 3 do 90 znaków",
//            "message_type"=>info
//        ]);
//
//        $fromDate = $v->validate($fromDate, [
//
//        ])
    }

    private function renderTemplate($template) {
        App::getSmarty()->display($template);
    }
}
