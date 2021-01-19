<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;

// TODO add functionality which allows user to click name of the template and display data about it (text and so on)

class EmailTemplateController {

    public function action_showEmailTemplates() {
        App::getSmarty()->assign("description", "Szablony wiadomości email");
        App::getSmarty()->assign("templates", $this->getTemplates());
        $this->renderTemplate("emailTemplatesTable.tpl");
    }

    public function action_addEmailTemplate() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $templateName = ParamUtils::getFromPost("template_name");
            $templateSubject = ParamUtils::getFromPost("template_subject");
            $templateText = ParamUtils::getFromPost("template_text");
            if ($this->validateEmailTemplateData($templateName, $templateSubject, $templateText)) {
                $this->addTemplate($templateName, $templateText, $templateSubject);
                App::getMessages()->addMessage(new Message("Pomyślnie dodano szablon wiadomości", Message::INFO));
            }
        }
        App::getSmarty()->assign("description", "Dodaj szablon");
        $this->renderTemplate("emailTemplateForm.tpl");
    }

    public function action_editEmailTemplate() {

    }

    public function action_deleteEmailTemplate() {
        $templateUuid = ParamUtils::getFromPost("email_template_uuid");
        $v = new Validator();

        if ($v->validateUuid($templateUuid)) {
            $result = $this->deleteTemplate($templateUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto szablon", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć szablonu", Message::ERROR));
            }
        }
        App::getRouter()->forwardTo("showEmailTemplates");
    }

    private function getTemplates() {
        return App::getDB()->select("email_template", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function addTemplate($templateName, $templateSubject, $templateText) {
        return App::getDB()->insert("email_template", [
            "uuid"=>generate_uuid(),
            "name"=>$templateName,
            "subject"=>$templateSubject,
            "text"=>$templateText,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function deleteTemplate($templateUuid) {
        $data = App::getDB()->delete("email_template", [
            "uuid"=>$templateUuid,
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
        return $data->rowCount();
    }

    private function validateEmailTemplateData(&$templateName, &$templateSubject, &$templateText) {
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

    private function renderTemplate($template) {
        App::getSmarty()->display($template);
    }
}
