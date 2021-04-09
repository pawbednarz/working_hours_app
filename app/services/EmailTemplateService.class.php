<?php

namespace app\services;

use core\App;
use core\Message;
use core\SessionUtils;
use core\Validator;

class EmailTemplateService {

    public function getTemplates() {
        return App::getDB()->select("email_template", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function getTemplate($templateUuid) {
        $data = App::getDB()->select("email_template", "*", [
            "uuid"=>$templateUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data[0];
    }

    public function addTemplate($templateName, $templateSubject, $templateText) {
        return App::getDB()->insert("email_template", [
            "uuid"=>generate_uuid(),
            "name"=>$templateName,
            "subject"=>$templateSubject,
            "text"=>$templateText,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function editTemplate($templateUuid, $templateName, $templateSubject, $templateText) {
        return App::getDB()->update("email_template", [
            "name"=>$templateName,
            "subject"=>$templateSubject,
            "text"=>$templateText
        ], [
            "uuid"=>$templateUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    public function deleteTemplate($templateUuid) {
        $data = App::getDB()->delete("email_template", [
            "uuid"=>$templateUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data->rowCount();
    }

    public function templateExist($templateUuid) {
        $result = App::getDB()->has("email_template", [
            "uuid"=>$templateUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Szablon o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    public function validateEmailTemplateData(&$templateName, &$templateSubject, &$templateText) {
        $v = new Validator();

        $templateName = $v->validate($templateName, [
            "required"=>"true",
            "required_message"=>'"Nazwa szablonu" jest wymagana',
            "trim"=>"true",
            "escape"=>"true",
            "min_length"=>3,
            "max_length"=>50,
            "validator_message"=>'"Nazwa szablonu" musi zawierać od 3 do 50 znaków'
        ]);

        $templateSubject = $v->validate($templateSubject, [
            "required"=>"true",
            "required_message"=>'"Temat" jest wymagany',
            "trim"=>"true",
            "escape"=>"true",
            "min_length"=>3,
            "max_length"=>60,
            "validator_message"=>'"Temat" musi zawierać od 3 do 60 znaków'
        ]);

        $templateText = $v->validate($templateText, [
            "required"=>"true",
            "required_message"=>'"Tekst wiadomości" jest wymagany',
            "trim"=>"true",
            "escape"=>"true",
            "min_length"=>10,
            "max_length"=>1000,
            "validator_message"=>'"Tekst wiadomości" musi zawierać od 10 do 1000 znaków'
        ]);

        return !App::getMessages()->isError();
    }

    public function renderEmailTemplatesTable() {
        App::getSmarty()->assign("description", "Szablony wiadomości email");
        App::getSmarty()->assign("templates", $this->getTemplates());
        App::getSmarty()->display("emailTemplatesTable.tpl");
    }

    public function renderAddEmailTemplateForm() {
        App::getSmarty()->assign("description", "Dodaj szablon");
        App::getSmarty()->display("addEmailTemplateForm.tpl");
    }

    public function renderEditEmailTemplateForm($templateUuid) {
        App::getSmarty()->assign("description", "Edytuj szablon");
        App::getSmarty()->assign("template", $this->getTemplate($templateUuid));
        App::getSmarty()->display("editEmailTemplateForm.tpl");

    }
}
