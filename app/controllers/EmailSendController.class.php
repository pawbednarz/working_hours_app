<?php

namespace app\controllers;

use app\services\EmailSendService;
use app\services\EmailTemplateService;
use app\services\RecipientService;
use app\services\ReportService;
use core\App;
use core\Message;
use core\ParamUtils;
use core\Validator;
use PHPMailer\PHPMailer\PHPMailer;

class EmailSendController {

    private $emailSendService;
    private $emailTemplateService;
    private $recipientService;
    private $reportService;

    function __construct() {
        $this->emailSendService = new EmailSendService();
    }

    public function action_showEmails() {
        $this->emailSendService->renderEmailsTable();
    }

    public function action_showEmail() {
        $emailUuid = ParamUtils::getFromGet("email_uuid");
        $v = new Validator();

        if ($v->validateUuid($emailUuid) && $this->emailSendService->emailExist($emailUuid)) {
            $this->emailSendService->renderShowEmail($emailUuid);
            exit();
        }
        $this->emailSendService->renderEmailsTable();
    }

    public function action_sendEmail() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $templateUuid = ParamUtils::getFromPost("email_template_uuid");
            $recipientUuid = ParamUtils::getFromPost("recipient_uuid");
            $reportUuid = ParamUtils::getFromPost("report_uuid");
            $v = new Validator();

            if ($v->validateUuid($templateUuid) && $v->validateUuid($recipientUuid) && $v->validateUuid($reportUuid)) {
                $this->emailTemplateService = new EmailTemplateService();
                $this->reportService = new ReportService();
                $this->recipientService = new RecipientService();
                $template = $this->emailTemplateService->getTemplate($templateUuid);
                $recipient = $this->recipientService->getRecipient($recipientUuid);
                $report = $this->reportService->getReport($reportUuid);

                $wasSent = $this->emailSendService->sendEmail($template["subject"], $template["text"], $recipient,
                    App::getConf()->reports_path . $report["filename"]);
                if ($wasSent) {
                    $this->emailSendService->addEmail($template["subject"], $template["text"], $report["uuid"], $recipient["uuid"]);
                }
            }
        }
        $this->emailSendService->renderSendEmail();
    }

    public function action_deleteEmail() {
        $emailUuid = ParamUtils::getFromPost("email_uuid");
        $v = new Validator();

        if ($v->validateUuid($emailUuid) && $this->emailSendService->emailExist($emailUuid)) {
            $result = $this->emailSendService->deleteEmail($emailUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto wiadomość", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć wiadomośći", Message::ERROR));
            }
        }
        $this->emailSendService->renderEmailsTable();
    }
}
