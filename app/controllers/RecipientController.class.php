<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;

class RecipientController {

    public function action_showRecipients() {
        App::getSmarty()->assign("description", "Odbiorcy emaili");
        App::getSmarty()->assign("recipients", $this->getRecipients());
        $this->renderTemplate("recipientsDashboard.tpl");
    }

    public function action_addRecipient() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $firstName = ParamUtils::getFromPost("first_name");
            $lastName = ParamUtils::getFromPost("last_name");
            $email = ParamUtils::getFromPost("email");

            if ($this->validateRecipientData($firstName, $lastName, $email)) {
                $this->addRecipient($firstName, $lastName, $email);
                App::getMessages()->addMessage(new Message("Pomyślnie dodano odbiorcę", Message::INFO));
            }
        }
        App::getSmarty()->assign("description", "Dodaj odbiorcę");
        $this->renderTemplate("addRecipient.tpl");
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
        App::getSmarty()->assign("description", "Odbiorcy emaili");
        App::getSmarty()->assign("recipients", $this->getRecipients());
        $this->renderTemplate("recipientsDashboard.tpl");
    }

    private function getRecipients() {
        return App::getDB()->select("recipient", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function addRecipient($firstName, $lastName, $email) {
        return App::getDB()->insert("recipient", [
            "uuid"=>generate_uuid(),
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email,
            "user_uuid"=>SessionUtils::load("userUuid", load)
        ]);
    }

    private function deleteRecipient($recipientUuid) {
        return App::getDB()->delete("recipient", [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function validateRecipientData(&$firstName, &$lastName, &$email) {
        $v = new Validator();
        $firstName = $v->validate($firstName, [
            "required"=>"true",
            "required_message"=>'"Imię" jest wymagane',
            "escape"=>"true",
            "trim"=>"true",
            "min_length"=>2,
            "max_length"=>60,
            "validator_message"=>'"Imię" powinno zawierać od 2 do 60 znaków'
        ]);

        $lastName = $v->validate($lastName, [
            "required"=>"true",
            "required_message"=>'"Nazwisko" jest wymagane',
            "escape"=>"true",
            "trim"=>"true",
            "min_length"=>2,
            "max_length"=>80,
            "validator_message"=>'"Nazwisko" powinno zawierać od 2 do 80 znaków'
        ]);

        $email = $v->validate($email, [
            "required"=>"true",
            "required_message"=>'"Email" jest wymagany',
            "escape"=>"true",
            "email"=>"true",
            "trim"=>"true",
            "min_length"=>5,
            "max_length"=>60,
            "validator_message"=>'"Email" powinno zawierać od 2 do 60 znaków'
        ]);

        return !App::getMessages()->isError();
    }

    private function renderTemplate($template) {
        App::getSmarty()->display($template);
    }

}
