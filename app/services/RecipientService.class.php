<?php

namespace app\services;

use core\App;
use core\Message;
use core\SessionUtils;
use core\Validator;

class RecipientService {
    public function getRecipients() {
        return App::getDB()->select("recipient", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getRecipient($recipientUuid) {
        $data = App::getDB()->select("recipient", "*", [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data[0];
    }

    public function addRecipient($firstName, $lastName, $email) {
        return App::getDB()->insert("recipient", [
            "uuid"=>generate_uuid(),
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function editRecipient($recipientUuid, $firstName, $lastName, $email) {
        return App::getDB()->update("recipient", [
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email
        ], [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function deleteRecipient($recipientUuid) {
        $data = App::getDB()->delete("recipient", [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data->rowCount();
    }

    public function recipientExist($recipientUuid) {
        $result = App::getDB()->has("recipient", [
            "uuid"=>$recipientUuid
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Odbiorca o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    public function validateRecipientData(&$firstName, &$lastName, &$email) {
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

    public function renderRecipientsTable() {
        App::getSmarty()->assign("description", "Odbiorcy emaili");
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->display("recipientsTable.tpl");
    }

    public function renderAddRecipientForm() {
        App::getSmarty()->assign("description", "Dodaj odbiorcę");
        App::getSmarty()->display("addRecipientForm.tpl");
    }

    public function renderEditRecipientForm($recipientUuid) {
        App::getSmarty()->assign("description", "Edytuj odbiorcę");
        App::getSmarty()->assign("recipient", $this->getRecipient($recipientUuid));
        App::getSmarty()->display("editRecipientForm.tpl");
    }
}
