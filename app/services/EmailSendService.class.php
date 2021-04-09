<?php

namespace app\services;

use core\App;
use core\Message;
use core\SessionUtils;

class EmailSendService {

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

    public function getOneFromDb ($table, $uuid) {
        return App::getDB()->select($table, "*", [
            "uuid"=>$uuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ])[0];
    }

    public function addEmail($subject, $text, $reportUuid, $recipientUuid) {
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

    public function deleteEmail($emailUuid) {
        $relationDelete = App::getDB()->delete("sent_email_recipient", [
            "email_uuid"=>$emailUuid
        ]);
        $emailDelete = App::getDB()->delete("sent_email", [
            "uuid"=>$emailUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $relationDelete && $emailDelete;
    }

    public function emailExist($emailUuid) {
        $result =  App::getDB()->has("sent_email", [
            "uuid"=>$emailUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Email o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    public function sendEmail($subject, $text, $recipient, $reportPath) {
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
        return $result;
    }

    public function renderEmailsTable() {
        App::getSmarty()->assign("description", "Wysłane maile");
        App::getSmarty()->assign("emails", $this->getEmails());
        App::getSmarty()->display("emailsTable.tpl");
    }

    public function renderShowEmail($emailUuid) {
        App::getSmarty()->assign("description", "Email");
        App::getSmarty()->assign("email", $this->getEmail($emailUuid));
        App::getSmarty()->display("showEmail.tpl");
    }

    public function renderSendEmail() {
        $templateService = new EmailTemplateService();
        $recipientService = new RecipientService();
        $reportService = new ReportService();
        App::getSmarty()->assign("description", "Wyślij email");
        App::getSmarty()->assign("templates", $templateService->getTemplates());
        App::getSmarty()->assign("recipients", $recipientService->getRecipients());
        App::getSmarty()->assign("reports", $reportService->getReports());
        App::getSmarty()->display("sendEmail.tpl");
    }
}
