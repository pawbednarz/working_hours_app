<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;

class RecipientController {

    public function action_showRecipients() {
        $this->renderRecipientsTable();
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
        $this->renderAddRecipientForm();
    }

    public function action_editRecipient() {
        $recipientUuid = ParamUtils::getFromGet("recipient_uuid");
        $v = new Validator();

        if ($v->validateUuid($recipientUuid) && $this->recipientExist($recipientUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $firstName = ParamUtils::getFromPost("first_name");
                $lastName = ParamUtils::getFromPost("last_name");
                $email = ParamUtils::getFromPost("email");

                if ($this->validateRecipientData($firstName, $lastName, $email)) {
                    $this->editRecipient($recipientUuid, $firstName, $lastName, $email);
                    App::getMessages()->addMessage(new Message("Pomyślnie edytowano odbiorcę", Message::INFO));
                }
            }
            $this->renderEditRecipientForm($recipientUuid);
            exit();
        }
        $this->renderRecipientsTable();
    }

    public function action_deleteRecipient() {
        $recipientUuid = ParamUtils::getFromPost("recipient_uuid");
        $v = new Validator();

        if ($v->validateUuid($recipientUuid)) {
            $result = $this->deleteRecipient($recipientUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto odbiorcę", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć odbiorcy", Message::ERROR));
            }
        }
        $this->renderRecipientsTable();
    }

    private function getRecipients() {
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

    private function addRecipient($firstName, $lastName, $email) {
        return App::getDB()->insert("recipient", [
            "uuid"=>generate_uuid(),
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function editRecipient($recipientUuid, $firstName, $lastName, $email) {
        return App::getDB()->update("recipient", [
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email
        ], [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function deleteRecipient($recipientUuid) {
        $data = App::getDB()->delete("recipient", [
            "uuid"=>$recipientUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data->rowCount();
    }

    private function recipientExist($recipientUuid) {
        $result = App::getDB()->has("recipient", [
            "uuid"=>$recipientUuid
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Odbiorca o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
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

    private function renderRecipientsTable() {
        App::getSmarty()->assign("description", "Odbiorcy emaili");
        App::getSmarty()->assign("recipients", $this->getRecipients());
        App::getSmarty()->display("recipientsTable.tpl");
    }

    private function renderAddRecipientForm() {
        App::getSmarty()->assign("description", "Dodaj odbiorcę");
        App::getSmarty()->display("addRecipientForm.tpl");
    }

    private function renderEditRecipientForm($recipientUuid) {
        App::getSmarty()->assign("description", "Edytuj odbiorcę");
        App::getSmarty()->assign("recipient", $this->getRecipient($recipientUuid));
        App::getSmarty()->display("editRecipientForm.tpl");
    }

}
