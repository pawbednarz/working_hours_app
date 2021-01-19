<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\Validator;

class AdminController {

    public function action_adminDashboard() {
        App::getSmarty()->assign("description", "Panel administratora");
        App::getSmarty()->assign("users", $this->getUsers());
        $this->renderTemplate("adminDashboard.tpl");
    }

    public function action_addUser() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $firstName = ParamUtils::getFromPost("first_name");
            $lastName = ParamUtils::getFromPost("last_name");
            $email = ParamUtils::getFromPost("email");
            $password = ParamUtils::getFromPost("password");
            // TODO check why select is not being sent from frontend
            $role = (ParamUtils::getFromPost("role") === "admin") ? "admin" : "user";

            if ($this->validateUserData($firstName, $lastName, $email, $password, $role)) {
                $this->addUser($firstName, $lastName, $email, $password, $role);
                App::getMessages()->addMessage(new Message("Pomyslnie dodano użytkownika", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się dodać użytkownika", Message::INFO));
            }
        }
        App::getSmarty()->assign("description", "Dodaj użytkownika");
        $this->renderTemplate("addUser.tpl");
    }

    public function action_editUser() {

    }

    public function action_deleteUser() {

    }

    private function getUsers() {
        return App::getDB()->select("user", "*");
    }

    private function addUser($firstName, $lastName, $email, $password, $role) {
        $pbkdf2 = hash_pbkdf2("sha512", $password, "", 15000); // use pbkdf2 to hash password
        try {
            App::getDB()->insert("user", [
                "uuid"=>generate_uuid(),
                "first_name"=>$firstName,
                "last_name"=>$lastName,
                "email"=>$email,
                "password"=>$pbkdf2,
                "role"=>$role
            ]);
        } catch (\PDOException $e) {
            // TODO write to logs
            // echo $e;
            App::getMessages()->addMessage("Wystąpił błąd podczas dodawania użytkownika. Spróbuj ponownie, lub skontaktuj się z administratorem systemu");
        }
    }

    private function validateUserData(&$firstName, &$lastName, &$email, &$password, &$role) {
        $v = new Validator();
        $firstName = $v->validate($firstName, [
            "required"=>"true",
            "required_message"=>'"Imię" jest wymagane',
            "escape"=>"true",
            "trim"=>"true",
            "min_length"=>3,
            "max_length"=>60,
            "validator_message"=>'"Imię" powinno mieć od 3 do 60 znaków'
        ]);

        $lastName = $v->validate($lastName, [
            "required"=>"true",
            "required_message"=>'"Nazwisko" jest wymagane',
            "escape"=>"true",
            "trim"=>"true",
            "min_length"=>3,
            "max_length"=>80,
            "validator_message"=>'"Imię" powinno mieć od 3 do 80 znaków'
        ]);

        $email = $v->validate($email, [
            "required"=>"true",
            "required_message"=>'"Email" jest wymagany',
            "escape"=>"true",
            "trim"=>"true",
            "email"=>"true",
            "min_length"=>8,
            "max_length"=>60,
            "validator_message"=>'"Email" powinien mieć od 8 do 60 znaków i mieć format: abc@abc.abc'
        ]);

        // TODO implement stronger password policy
        $password = $v->validate($password, [
            "required"=>"true",
            "required_message"=>'"Hasło" jest wymagane',
            "min_length"=>10,
            "max_length"=>256,
            "validator_message"=>'"Hasło" powinno mieć co najmniej 10 znaków'
        ]);

        if (!in_array($role, ["admin", "user"])) {
            App::getMessages()->addMessage(new Message("Jedyne dozwolone role to admin oraz user", Message::ERROR));
        }

        return !App::getMessages()->isError();
    }

    private function renderTemplate($template) {
        App::getSmarty()->display($template);
    }
}
