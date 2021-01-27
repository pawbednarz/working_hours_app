<?php

namespace app\controllers;

use core\App;
use core\SessionUtils;

class EmailSendController {

    public function action_showEmails() {
        $this->renderEmailsTable();
    }

    public function action_showEmail() {

    }

    public function action_sendEmail() {
        $this->renderSendEmail();
    }

    private function getEmails() {
        return App::getDB()->select("sent_email", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
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

    private function renderEmailsTable() {
        App::getSmarty()->assign("description", "Wysłane maile");
        App::getSmarty()->assign("emails", $this->getEmails());
        App::getSmarty()->display("emailsTable.tpl");
    }

    private function renderSendEmail() {
        App::getSmarty()->assign("description", "Wyślij email");
        App::getSmarty()->assign("templates", $this->getTemplates());
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->assign("reports", $this->getReports());
        App::getSmarty()->display("sendEmail.tpl");
    }
}
