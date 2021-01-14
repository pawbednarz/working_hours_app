<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\SessionUtils;
use core\Validator;

class RecipientController {

    public function action_showRecipients() {
        App::getSmarty()->assign("description", "Odbiorcy emaili");
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->display("recipientsDashboard.tpl");
    }

    public function action_addRecipient() {

    }

    public function action_editRecipient() {

    }

    public function action_deleteRecipient() {
        $v = new Validator();
        $recipientUuid = $v->validateFromPost("recipient_uuid", [
            "required"=>true,
            "required_message"=>"Nie podano UUID odbiorcy do usunięcia",
            "min_lenght"=>36,
            "max_lenhht"=>36,
            "regexp"=>"/[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}/",
            "validator_message"=>"UUID musi składać się z 36 znaków i mieć format xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
        ]);

        if ($v->isLastOK()) {
            $result = $this->deleteRecipient($recipientUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto odbiorcę", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć odbiorcy", Message::ERROR));
            }
        }
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->display("recipientsDashboard.tpl");
    }

    private function getRecipients() {
        return App::getDB()->select("recipient", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function deleteRecipient($recipientUuid) {
        return App::getDB()->delete("recipient", [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

}
