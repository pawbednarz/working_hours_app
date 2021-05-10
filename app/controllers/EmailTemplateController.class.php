<?php

namespace app\controllers;

use app\services\EmailTemplateService;
use core\App;
use core\Message;
use core\ParamUtils;
use core\SessionUtils;
use core\Validator;

// TODO add functionality which allows user to click name of the template and display data about it (text and so on)

class EmailTemplateController {

    private $emailTemplateService;

    function __construct() {
        $this->emailTemplateService = new EmailTemplateService();
    }

    public function action_showEmailTemplates() {
        $this->emailTemplateService->renderEmailTemplatesTable();
    }

    public function action_addEmailTemplate() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $templateName = ParamUtils::getFromPost("template_name");
            $templateSubject = ParamUtils::getFromPost("template_subject");
            $templateText = ParamUtils::getFromPost("template_text");
            if ($this->emailTemplateService->validateEmailTemplateData($templateName, $templateSubject, $templateText)) {
                $this->emailTemplateService->addTemplate($templateName, $templateSubject, $templateText);
                App::getMessages()->addMessage(new Message("Pomyślnie dodano szablon wiadomości", Message::INFO));
            }
        }
        $this->emailTemplateService->renderAddEmailTemplateForm();
    }

    public function action_editEmailTemplate() {
        $templateUuid = ParamUtils::getFromGet("email_template_uuid");
        $v = new Validator();

        if ($v->validateUuid($templateUuid) && $this->emailTemplateService->templateExist($templateUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $templateName = ParamUtils::getFromPost("template_name");
                $templateSubject = ParamUtils::getFromPost("template_subject");
                $templateText = ParamUtils::getFromPost("template_text");

                if ($this->emailTemplateService->validateEmailTemplateData($templateName, $templateSubject, $templateText)) {
                    $this->emailTemplateService->editTemplate($templateUuid, $templateName, $templateSubject, $templateText);
                    App::getMessages()->addMessage(new Message("Pomyślnie edytowano szablon", Message::INFO));
                }
            }
            $this->emailTemplateService->renderEditEmailTemplateForm($templateUuid);
            exit();
        }
        $this->emailTemplateService->renderEmailTemplatesTable();
    }

    public function action_deleteEmailTemplate() {
        $templateUuid = ParamUtils::getFromPost("email_template_uuid");
        $v = new Validator();

        if ($v->validateUuid($templateUuid)) {
            $result = $this->emailTemplateService->deleteTemplate($templateUuid);
            if ($result) {
                App::getMessages()->addMessage(new Message("Pomyślnie usunięto szablon", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się usunąć szablonu", Message::ERROR));
            }
        }
        $this->emailTemplateService->renderEmailTemplatesTable();
    }
}
