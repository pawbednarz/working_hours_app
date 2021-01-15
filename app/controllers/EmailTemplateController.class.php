<?php

namespace app\controllers;

use core\App;
use core\SessionUtils;

class EmailTemplateController {

    public function action_showEmailTemplates() {

        App::getSmarty()->assign("templates", $this->getTemplates());
        $this->renderTemplate("emailTemplateDashboard.tpl");
    }

    public function action_addEmailTemplate() {

    }

    public function action_editEmailTemplate() {

    }

    public function action_deleteEmailTemplate() {

    }

    private function getTemplates() {
        return App::getDB()->select("email_template", "*", [
            "user_uuid"=>SessionUtils::load("userUuid", true)
        ]);
    }

    private function renderTemplate($template) {
        App::getSmarty()->display($template);
    }
}
