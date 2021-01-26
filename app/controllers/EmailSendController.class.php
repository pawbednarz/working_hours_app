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

    }

    private function getEmails() {
        return App::getDB()->select("sent_email", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function renderEmailsTable() {
        App::getSmarty()->assign("description", "Wysłane maile");
        App::getSmarty()->assign("emails", $this->getEmails());
        App::getSmarty()->display("emailsTable.tpl");
    }

}
