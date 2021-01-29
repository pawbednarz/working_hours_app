<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;
use MongoDB\Driver\Session;
use PHPMailer\PHPMailer\PHPMailer;

class EmailSendController {

    public function action_showEmails() {
        $this->renderEmailsTable();
    }

    public function action_showEmail() {
        $emailUuid = ParamUtils::getFromGet("email_uuid");
        $v = new Validator();

        if ($v->validateUuid($emailUuid) && $this->emailExist($emailUuid)) {
            $this->renderShowEmail($emailUuid);
        }
    }

    public function action_sendEmail() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $templateUuid = ParamUtils::getFromPost("email_template_uuid");
            $recipientUuid = ParamUtils::getFromPost("recipient_uuid");
            $reportUuid = ParamUtils::getFromPost("report_uuid");
            $v = new Validator();

            if ($v->validateUuid($templateUuid) && $v->validateUuid($recipientUuid) && $v->validateUuid($reportUuid)) {
                $template = $this->getOneFromDb("email_template", $templateUuid);
                $recipient = $this->getOneFromDb("recipient", $recipientUuid);
                $report = $this->getOneFromDb("report", $reportUuid);

                $this->sendEmail($template["subject"], $template["text"], $recipient,
                    App::getConf()->reports_path . $report["filename"]);
                $this->addEmail($template["subject"], $template["text"], $report["uuid"], $recipient["uuid"]);
            }
        }
        $this->renderSendEmail();
    }

    public function action_deleteEmail() {
        $emailUuid = ParamUtils::getFromPost("email_uuid");
        $v = new Validator();

        if ($v->validateUuid($emailUuid) && $this->emailExist($emailUuid)) {
            $result = $this->deleteEmail($emailUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto wiadomość", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć wiadomośći", Message::ERROR));
            }
        }
        $this->renderEmailsTable();
    }

    private function getEmails() {
        return App::getDB()->select("sent_email", [
            "[>]sent_email_recipient"=>["uuid"=>"email_uuid"],
            "[>]recipient"=>["sent_email_recipient.recipient_uuid"=>"uuid"],
            "[>]report"=>["report_uuid"=>"uuid"]
        ], [
            "sent_email.uuid",
            "sent_date",
            "subject",
            "text",
            "report"=>[
                "filename",
                "from_date",
                "to_date"
            ],
            "recipient"=>[
                "first_name",
                "last_name",
                "email"
            ]
        ], [
            "sent_email.user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getEmail($emailUuid) {
        return App::getDB()->select("sent_email", [
            "[>]sent_email_recipient"=>["uuid"=>"email_uuid"],
            "[>]recipient"=>["sent_email_recipient.recipient_uuid"=>"uuid"],
            "[>]report"=>["report_uuid"=>"uuid"]
        ], [
            "sent_date",
            "subject",
            "text",
            "report"=>[
                "filename",
                "from_date",
                "to_date"
            ],
            "recipient"=>[
                "first_name",
                "last_name",
                "email"
            ]
        ], [
            "sent_email.uuid"=>$emailUuid,
            "sent_email.user_uuid"=>SessionUtils::load("userUuid", true)
        ])[0];
    }

    private function getTemplates() {
        return App::getDB()->select("email_template", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getRecipients() {
        return App::getDB()->select("recipient", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getReports() {
        return App::getDB()->select("report", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getOneFromDb ($table, $uuid) {
        return App::getDB()->select($table, "*", [
            "uuid"=>$uuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ])[0];
    }

    private function addEmail($subject, $text, $reportUuid, $recipientUuid) {
        $emailUuid = generate_uuid();
        App::getDB()->insert("sent_email", [
            "uuid"=>$emailUuid,
            "sent_date"=>date("Y-m-d H:i"),
            "subject"=>$subject,
            "text"=>$text,
            "user_uuid"=>SessionUtils::load("userUuid", true),
            "report_uuid"=>$reportUuid
        ]);

        App::getDB()->insert("sent_email_recipient", [
            "uuid"=>generate_uuid(),
            "email_uuid"=>$emailUuid,
            "recipient_uuid"=>$recipientUuid
        ]);
    }

    private function deleteEmail($emailUuid) {
        $relationDelete = App::getDB()->delete("sent_email_recipient", [
            "email_uuid"=>$emailUuid
        ]);
        $emailDelete = App::getDB()->delete("sent_email", [
            "uuid"=>$emailUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $relationDelete && $emailDelete;
    }

    private function emailExist($emailUuid) {
        return App::getDB()->has("sent_email", [
            "uuid"=>$emailUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function sendEmail($subject, $text, $recipient, $reportPath) {
        require_once App::getConf()->root_path . '/lib/PHPMailer/PHPMailer.php';
        require_once App::getConf()->root_path . '/lib/PHPMailer/SMTP.php';
        require_once App::getConf()->root_path . '/lib/PHPMailer/Exception.php';

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->CharSet = "UTF-8";
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->isHTML();
        $mail->Host = App::getConf()->smtp_host;
        $mail->Port = App::getConf()->smtp_port;
        $mail->Username = App::getConf()->smtp_username;
        $mail->Password = App::getConf()->smtp_password;

        $mail->SetFrom(App::getConf()->smtp_username,'Raport eHarmonogram');
        $mail->Subject = $subject;
        $mail->Body = $text;
        $mail->AddAttachment($reportPath);
        $mail->AddAddress($recipient["email"]);

        $result = $mail->Send();

        if ($result) {
            App::getMessages()->addMessage(new Message("Wysłano wiadomość z raportem", Message::INFO));
        } else {
            App::getMessages()->addMessage(new Message("Nie udało się wysłać wiadomości", Message::ERROR));
        }
    }

    private function renderEmailsTable() {
        App::getSmarty()->assign("description", "Wysłane maile");
        App::getSmarty()->assign("emails", $this->getEmails());
        App::getSmarty()->display("emailsTable.tpl");
    }

    private function renderShowEmail($emailUuid) {
        App::getSmarty()->assign("description", "Email");
        App::getSmarty()->assign("email", $this->getEmail($emailUuid));
        App::getSmarty()->display("showEmail.tpl");
    }

    private function renderSendEmail() {
        App::getSmarty()->assign("description", "Wyślij email");
        App::getSmarty()->assign("templates", $this->getTemplates());
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->assign("reports", $this->getReports());
        App::getSmarty()->display("sendEmail.tpl");
    }
}
