<?php

namespace app\controllers;

use app\services\EntryService;
use core\App;
use core\Message;
use core\ParamUtils;
use core\RoleUtils;
use core\Validator;

class EntryController {

    private $entryService;

    function __construct() {
        $this->entryService = new EntryService();
    }

    public function action_dashboard() {
        if (RoleUtils::inRole("admin")) {
            App::getRouter()->redirectTo("adminDashboard");
        }
        $this->entryService->renderEntriesTable();
    }

    public function action_showEntries() {
        $this->entryService->getEntries();
    }

    public function action_addEntry() {
        // TODO add form field for work description
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
            $validationResult = $this->entryService->validateEntryData($place, $fromDate, $toDate, $fromTime,
                $toTime, $dayOff);

            if ($validationResult) {
                if ($dayOff) {
                    $this->entryService->addDayOffEntry($fromDate);
                } else {
                    $fromDateAndTime = $this->entryService->formatDateAndTime($fromDate, $fromTime);
                    $toDateAndTime = $this->entryService->formatDateAndTime($toDate, $toTime);
                    $hours = $this->entryService->countHoursDifference($fromDateAndTime, $toDateAndTime);

                    $this->entryService->addEntry($place, $fromDateAndTime, $toDateAndTime, $hours, $wasDriver, $subAllowance, 0);
                }
            }
        }
        $this->entryService->renderAddEntryForm();
    }

    public function action_editEntry() {
        $entryUuid = ParamUtils::getFromGet("entry_uuid");
        $v = new Validator();

        if ($v->validateUuid($entryUuid) && $this->entryService->entryExist($entryUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $place = ParamUtils::getFromPost("place");
                $fromDate = ParamUtils::getFromPost("date_from");
                $toDate = ParamUtils::getFromPost("date_to");
                $fromTime = ParamUtils::getFromPost("time_from");
                $toTime = ParamUtils::getFromPost("time_to");
                $wasDriver = ParamUtils::getFromPost("driver") == "true";
                $subAllowance = ParamUtils::getFromPost("subsistence_allowance") == "true";
                $dayOff = ParamUtils::getFromPost("day_off") == "true";
                $validationResult = $this->entryService->validateEntryData($place, $fromDate, $toDate, $fromTime,
                    $toTime, $dayOff);
                if ($validationResult) {
                    if ($dayOff) {
                        $this->entryService->editToDayOffEntry($entryUuid, $fromDate);
                    } else {
                        $fromDateAndTime = $this->entryService->formatDateAndTime($fromDate, $fromTime);
                        $toDateAndTime = $this->entryService->formatDateAndTime($toDate, $toTime);
                        $hours = $this->entryService->countHoursDifference($fromDateAndTime, $toDateAndTime);
                        $this->entryService->editEntry($entryUuid, $place, $fromDateAndTime, $toDateAndTime, $hours, $wasDriver, $subAllowance, $dayOff);
                    }
                }
            }
            $this->entryService->renderEditEntryForm($entryUuid);
            exit();
        }
        $this->entryService->renderEntriesTable();
    }

    public function action_deleteEntry() {
        $entryUuid = ParamUtils::getFromPost("entry_uuid");
        $v = new Validator();

        if ($v->validateUuid($entryUuid)) {
            $result = $this->entryService->deleteEntry($entryUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto wpis", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć wpisu", Message::ERROR));
            }
        }
        $this->entryService->renderEntriesTable();
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
            if ($v->isLastOK()) {
                $inputDate = $this->entryService->getYearAndMonth($dateFrom);
                $year = $inputDate[0];
                $month = $inputDate[1];
                $this->entryService->renderMonthEntriesTable($year, $month);
                exit();
            }
        }
        $this->entryService->renderChooseEntryMonth();
    }

    public function action_getEntriesAjaxPage() {
        $size = ParamUtils::getFromGet("size");
        $page = ParamUtils::getFromGet("page");
        $filter = ParamUtils::getFromGet("filter");
        $place = ParamUtils::getFromPost("place");
        $hours = ParamUtils::getFromPost("hours");
        $wasDriver = ParamUtils::getFromPost("driver");
        $subAllowance = ParamUtils::getFromPost("subsistence_allowance");
        $dayOff = ParamUtils::getFromPost("day_off");
        $this->entryService->renderAjaxEntriesPage($place, $hours, $wasDriver, $subAllowance, $dayOff, $filter, $size, $page);
    }
}
