<?php

namespace  app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;

class ReportController {

    public function action_showReports() {
        $this->renderReportsTable();
    }

    public function action_generateReport() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $fromDate = ParamUtils::getFromPost("date_from");
            $toDate = ParamUtils::getFromPost("date_to");

            if ($this->validateDates($fromDate, $toDate)) {
                $entries = $this->getEntries($fromDate, $toDate);
//                App::getSmarty()->assign("entries", $entries);
//                App::getSmarty()->display("entriesTable.tpl");
                $this->generateReport($entries);
            }
        }
        $this->renderGenerateReportForm();
    }

    private function getReports() {
        return App::getDB()->select("report", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getEntries($fromDate, $toDate) {
        // add one day to toDate, to get entries for toDate day too
        $toDate = date("Y-m-d", strtotime("+1 day", strtotime($toDate)));
        return App::getDB()->select("work_hour_entry", [
            "from_date",
            "to_date",
            "place",
            "hours",
            "was_driver",
            "subsistence_allowance",
            "day_off"
            ],[
            "from_date[<>]"=>[$fromDate, $toDate],
            "user_uuid"=>SessionUtils::load("userUuid", true),
            "ORDER"=>[
                "from_date"=>"ASC"
                ]
        ]);
    }

    private function generateReport($entryArray) {
        // head data for csv file
        $csvHead = array("Data", "Miejsce", "Od-Do", "Godziny", "Kierowca", "Dieta", "Dzien wolny");

        // open file and write head data
        $fp = fopen(App::getConf()->reports_path . "file1.csv", "w");
        fputcsv($fp, $csvHead);

        // write each entry to file
        foreach($entryArray as $entry) {
            $data = array(
                date("d.m.Y", strtotime($entry["from_date"])),
                $entry["place"],
                date("H:i", strtotime($entry["from_date"])) . "-" . date ("H:i", strtotime($entry["to_date"])),
                str_replace(".", ",", $entry["hours"]),
                $entry["was_driver"],
                $entry["subsistence_allowance"],
                $entry["day_off"]
            );
            fputcsv($fp, $data);
        }
        // write one blank row and sum all hours in next row
        fputcsv($fp, array());
        fputcsv($fp, array("", "", "", "=SUMA(D2:D" . (count($entryArray) + 1) . ")"));
        fclose($fp);
    }

    private function validateDates($fromDate, $toDate) {
        $v = new Validator();

        $fromDate = $v->validate($fromDate, [
            "required"=>"true",
            "required_message"=>'"Data od" jest wymagana',
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'"Data od" musi mieć format yyyy-mm-dd'
        ]);

        $toDate = $v->validate($toDate, [
            "required"=>"true",
            "required_message"=>'"Data do" jest wymagana',
            "min_length"=>10,
            "max_length"=>10,
            "date_format"=>"Y-m-d",
            "validator_message"=>'"Data do" musi mieć format yyyy-mm-dd'
        ]);

        $result = !App::getMessages()->isError();

        if ($result && $fromDate > $toDate) {
            $result = false;
            App::getMessages()->addMessage(new Message('"Data od" musi być wcześniejsza niż "Data do"', Message::ERROR));
        }
        return $result;
    }

    private function renderReportsTable() {
        App::getSmarty()->assign("description", "Raporty");
        App::getSmarty()->assign("reports", $this->getReports());
        App::getSmarty()->display("reportsTable.tpl");
    }

    private function renderGenerateReportForm() {
        App::getSmarty()->assign("description", "Wygeneruj raport");
        App::getSmarty()->display("generateReportForm.tpl");
    }
}
