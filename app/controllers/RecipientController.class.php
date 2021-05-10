<?php

namespace app\controllers;

use app\services\RecipientService;
use core\App;
use core\Message;
use core\ParamUtils;
use core\Validator;

class RecipientController {

    private $recipientService;

    function __construct() {
        $this->recipientService = new RecipientService();
    }

    public function action_showRecipients() {
        $this->recipientService->renderRecipientsTable();
    }

    public function action_addRecipient() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $firstName = ParamUtils::getFromPost("first_name");
            $lastName = ParamUtils::getFromPost("last_name");
            $email = ParamUtils::getFromPost("email");

            if ($this->recipientService->validateRecipientData($firstName, $lastName, $email)) {
                $this->recipientService->addRecipient($firstName, $lastName, $email);
                App::getMessages()->addMessage(new Message("Pomyślnie dodano odbiorcę", Message::INFO));
            }
        }
        $this->recipientService->renderAddRecipientForm();
    }

    public function action_editRecipient() {
        $recipientUuid = ParamUtils::getFromGet("recipient_uuid");
        $v = new Validator();

        if ($v->validateUuid($recipientUuid) && $this->recipientService->recipientExist($recipientUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $firstName = ParamUtils::getFromPost("first_name");
                $lastName = ParamUtils::getFromPost("last_name");
                $email = ParamUtils::getFromPost("email");

                if ($this->recipientService->validateRecipientData($firstName, $lastName, $email)) {
                    $this->recipientService->editRecipient($recipientUuid, $firstName, $lastName, $email);
                    App::getMessages()->addMessage(new Message("Pomyślnie edytowano odbiorcę", Message::INFO));
                }
            }
            $this->recipientService->renderEditRecipientForm($recipientUuid);
            exit();
        }
        $this->recipientService->renderRecipientsTable();
    }

    public function action_deleteRecipient() {
        $recipientUuid = ParamUtils::getFromPost("recipient_uuid");
        $v = new Validator();

        if ($v->validateUuid($recipientUuid)) {
            $result = $this->recipientService->deleteRecipient($recipientUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto odbiorcę", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć odbiorcy", Message::ERROR));
            }
        }
        $this->recipientService->renderRecipientsTable();
    }
}
