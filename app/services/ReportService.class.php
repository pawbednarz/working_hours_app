<?php

namespace app\services;

use core\App;
use core\Message;
use core\SessionUtils;
use core\Validator;

class ReportService {
    public function getReports() {
        return App::getDB()->select("report", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function getReport($reportUuid) {
        return App::getDB()->select("report", "*", [
            "uuid"=>$reportUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ])[0];
    }

    private function addReport($filename, $fromDate, $toDate) {
        return App::getDB()->insert("report", [
            "uuid"=>generate_uuid(),
            "filename"=>$filename,
            "creation_date"=>date("Y-m-d H:i"),
            "from_date"=>$fromDate,
            "to_date"=>$toDate,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function deleteReport($reportUuid) {
        $filename = $this->getReport($reportUuid)["filename"];
        return $this->deleteReportFromDb($reportUuid) && $this->deleteReportFromDisk($filename);
    }

    private function deleteReportFromDb($reportUuid) {
        $data = App::getDB()->delete("report", [
            "uuid"=>$reportUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        if (!$data->rowCount()) {
            App::getMessages()->addMessage(new Message("Nie udało się usunąć raportu z bazy danych", Message::ERROR));
        }
        return $data->rowCount();
    }

    private function deleteReportFromDisk($filename) {
        $result = unlink(App::getConf()->reports_path . $filename);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Nie udało się usunąć pliku raportu z serwera", Message::ERROR));
        }
        return $result;
    }

    public function reportExist($reportUuid) {
        $report = App::getDB()->select("report", [
            "filename"
        ],[
            "uuid"=>$reportUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        if (count($report) == 1 && file_exists(App::getConf()->reports_path . $report[0]["filename"])) {
            return true;
        }
        App::getMessages()->addMessage(new Message("Nie znaleziono raportu o podanym UUID", Message::ERROR));
        return false;
    }

    public function directoryExistAndCreate($path) {
        if (!file_exists($path)) {
            return mkdir($path, 0755, true);
        }
        return true;
    }

    public function getEntries($fromDate, $toDate) {
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

    private function checkReportsWithSameTime($hourAndMinute) {
        return App::getDB()->count("report", [
            "filename[~]"=> "%" . $hourAndMinute . "%",
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function generateReport($entryArray, $fromDate, $toDate) {
        $monthsPl = ["styczen", "luty", "marzec", "kwiecien", "maj", "czerwiec", "lipiec", "sierpien", "wrzesien",
            "pazdziernik", "listopad", "grudzien"];
        $monthPl = $monthsPl[intval(date("m")) - 1];
        // head data for csv file
        $csvHead = array("Data", "Miejsce", "Od-Do", "Godziny", "Kierowca", "Dieta", "Dzien wolny");
        $hourAndMinute = date("H_i");
        $reportsCount = $this->checkReportsWithSameTime($hourAndMinute);
        $filename = "";
        if ($reportsCount == 0) {
            $filename = "raport_" . $monthPl . "_" . date("H_i") . ".csv";
        } else if ($reportsCount > 0) {
            $filename = "raport_" . $monthPl . "_" . date("H_i") . "(" . $reportsCount  .").csv";
        }

        $path = App::getConf()->reports_path . $filename;
        // open file and write head data
        $fp = fopen($path, "w");
        fputcsv($fp, $csvHead);

        // write each entry to file
        foreach($entryArray as $entry) {
            if ($entry["day_off"]) {
                $data = array(
                    date("d.m.Y", strtotime($entry["from_date"])),
                    "---",
                    "---",
                    "---",
                    "---",
                    "---",
                    $entry["day_off"] ? "Tak" : ""
                );
            } else {
                $data = array(
                    date("d.m.Y", strtotime($entry["from_date"])),
                    $entry["place"],
                    date("H:i", strtotime($entry["from_date"])) . "-" . date ("H:i", strtotime($entry["to_date"])),
                    str_replace(".", ",", $entry["hours"]),
                    $entry["was_driver"] ? "Tak" : "",
                    $entry["subsistence_allowance"] ? "Tak" : "",
                    $entry["day_off"] ? "Tak" : ""
                );
            }
            fputcsv($fp, $data);
        }
        // write one blank row and sum all hours in next row
        fputcsv($fp, array());
        fputcsv($fp, array("", "", "", "=SUMA(D2:D" . (count($entryArray) + 1) . ")"));
        fclose($fp);
        // add report to database
        $this->addReport($filename, $fromDate, $toDate);
    }

    public function validateDates($fromDate, $toDate) {
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

    public function renderReportsTable() {
        App::getSmarty()->assign("description", "Raporty");
        App::getSmarty()->assign("reports", $this->getReports());
        App::getSmarty()->display("reportsTable.tpl");
    }

    public function renderGenerateReportForm() {
        App::getSmarty()->assign("description", "Wygeneruj raport");
        App::getSmarty()->display("generateReportForm.tpl");
    }
}
