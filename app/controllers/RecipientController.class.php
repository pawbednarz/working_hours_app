<?php

namespace app\controllers;

use core\App;
use core\SessionUtils;

class RecipientController {

    public function action_showRecipients() {
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->display("recipientsDashboard.tpl");
    }

    public function action_addRecipient() {

    }

    public function action_editRecipient() {

    }

    public function action_deleteRecipient() {

    }

    private function getRecipients() {
        return App::getDB()->select("recipient", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

}
