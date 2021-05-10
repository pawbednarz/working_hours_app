<?php

namespace  app\controllers;

use app\services\ReportService;
use core\App;
use core\Message;
use core\ParamUtils;
use core\Validator;

class ReportController {

    private $reportService;

    function __construct() {
        $this->reportService = new ReportService();
    }

    public function action_showReports() {
        $this->reportService->renderReportsTable();
    }

    public function action_generateReport() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // TODO fix formula injection vulnerability
            $fromDate = ParamUtils::getFromPost("date_from");
            $toDate = ParamUtils::getFromPost("date_to");

            if ($this->reportService->validateDates($fromDate, $toDate)) {
                if ($this->reportService->directoryExistAndCreate(App::getConf()->reports_path)){
                    $entries = $this->reportService->getEntries($fromDate, $toDate);
                    $this->reportService->generateReport($entries, $fromDate, $toDate);
                    App::getMessages()->addMessage(new Message("Pomyślnie wygenerowano raport", Message::INFO));
                } else {
                    App::getMessages()->addMessage(new Message("Nie udało się wygenerować raportu", Message::ERROR));
                }
            }
        }
        $this->reportService->renderGenerateReportForm();
    }

    public function action_downloadReport() {
        $reportUuid = ParamUtils::getFromGet("report_uuid");
        $v = new Validator();

        if ($v->validateUuid($reportUuid) && $this->reportService->reportExist($reportUuid)) {
            $report = $this->reportService->getReport($reportUuid);
            $filePath = App::getConf()->reports_path . $report["filename"];
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $report["filename"] . '";');
            header("Content-Length: " . filesize($filePath));
            ob_clean();
            flush();
            readfile($filePath);
        }
        $this->reportService->renderReportsTable();
    }

    public function action_deleteReport() {
        $reportUuid = ParamUtils::getFromPost("report_uuid");
        $v = new Validator();

        if ($v->validateUuid($reportUuid) && $this->reportService->reportExist($reportUuid)) {

            if ($this->reportService->deleteReport($reportUuid)) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto raport", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć raportu", Message::ERROR));
            }
        }
        $this->reportService->renderReportsTable();
    }
}
