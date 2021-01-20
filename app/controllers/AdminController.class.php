<?php

namespace app\controllers;

use core\App;
use core\Message;
use core\ParamUtils;
use core\Validator;

// TODO implement password change functionality

class AdminController {

    public function action_adminDashboard() {
        $this->renderUsersTable();
    }

    public function action_addUser() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $firstName = ParamUtils::getFromPost("first_name");
            $lastName = ParamUtils::getFromPost("last_name");
            $email = ParamUtils::getFromPost("email");
            $password = ParamUtils::getFromPost("password");
            $passwordRepeat = ParamUtils::getFromPost("password_repeat");
            $role = (ParamUtils::getFromPost("role") === "admin") ? "admin" : "user";
            $isActive = ParamUtils::getFromPost("is_active") == "true";

            if ($this->validateUserData($firstName, $lastName, $email, $password, $passwordRepeat, $role)) {
                $this->addUser($firstName, $lastName, $email, $password, $role, $isActive);
                App::getMessages()->addMessage(new Message("Pomyslnie dodano użytkownika", Message::INFO));
            } else {
                App::getMessages()->addMessage(new Message("Nie udało się dodać użytkownika", Message::INFO));
            }
        }
        $this->renderAddUserForm();
    }

    public function action_editUser()
    {
        $userUuid = ParamUtils::getFromGet("uuid");
        $v = new Validator();

        if ($v->validateUuid($userUuid) && $this->userExist($userUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "GET") {
                $this->renderUserEditForm($userUuid);
            } else if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $firstName = ParamUtils::getFromPost("first_name");
                $lastName = ParamUtils::getFromPost("last_name");
                $email = ParamUtils::getFromPost("email");
                $role = (ParamUtils::getFromPost("role") === "admin") ? "admin" : "user";
                $isActive = ParamUtils::getFromPost("is_active") == "true";

                if ($this->validateUserNameEmailAndRole($firstName, $lastName, $email, $role)) {
                    $this->editUser($userUuid, $firstName, $lastName, $email, $role, $isActive);
                    App::getMessages()->addMessage(new Message("Pomyślnie edytowano użytkownika", Message::INFO));
                }
                $this->renderUserEditForm($userUuid);
                exit();
            }
        }
        $this->renderUsersTable();
    }

    private function getUsers() {
        return App::getDB()->select("user", "*");
    }

    private function getUser($userUuid) {
        return App::getDB()->select("user", [
            "uuid",
            "first_name",
            "last_name",
            "email",
            "role",
            "is_active"
        ], [
            "uuid"=>$userUuid
        ]);
    }

    private function addUser($firstName, $lastName, $email, $password, $role, $isActive) {
        $pbkdf2 = hash_pbkdf2("sha512", $password, "", 15000); // use pbkdf2 to hash password
        try {
            App::getDB()->insert("user", [
                "uuid"=>generate_uuid(),
                "first_name"=>$firstName,
                "last_name"=>$lastName,
                "email"=>$email,
                "password"=>$pbkdf2,
                "role"=>$role,
                "is_active"=>$isActive
            ]);
        } catch (\PDOException $e) {
            // TODO write to logs
            // echo $e;
            App::getMessages()->addMessage("Wystąpił błąd podczas dodawania użytkownika. Spróbuj ponownie, lub skontaktuj się z administratorem systemu");
        }
    }

    private function editUser($uuid, $firstName, $lastName, $email, $role, $isActive) {
        return App::getDB()->update("user", [
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email,
            "role"=>$role,
            "is_active"=>$isActive
        ], [
            "uuid"=>$uuid
        ]);
    }

    private function userExist($userUuid) {
        $result = App::getDB()->has("user", [
            "uuid"=>$userUuid
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Użytkownik o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    private function validateUserData(&$firstName, &$lastName, &$email, &$password, $passwordRepeat, $role) {
        if ($password != $passwordRepeat) {
            App::getMessages()->addMessage(new Message("Wprowadzone hasła nie są takie same", Message::ERROR));
            return false;
        }

        $v = new Validator();
        // TODO implement stronger password policy
        $password = $v->validate($password, [
            "required"=>"true",
            "required_message"=>'"Hasło" jest wymagane',
            "min_length"=>10,
            "max_length"=>256,
            "validator_message"=>'"Hasło" powinno mieć co najmniej 10 znaków'
        ]);

        $this->validateUserNameEmailAndRole($firstName, $lastName, $email, $role);

        return !App::getMessages()->isError();
    }

    private function validateUserNameEmailAndRole(&$firstName, &$lastName, &$email, $role) {
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
            "validator_message"=>'"Nazwisko" powinno mieć od 3 do 80 znaków'
        ]);

        // TODO check if mail is unique
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

        if (!in_array($role, ["admin", "user"])) {
            App::getMessages()->addMessage(new Message("Jedyne dozwolone role to admin oraz user", Message::ERROR));
        }

        return !App::getMessages()->isError();
    }

    private function renderUsersTable() {
        App::getSmarty()->assign("description", "Panel administratora");
        App::getSmarty()->assign("users", $this->getUsers());
        App::getSmarty()->display("usersTable.tpl");
    }

    private function renderAddUserForm() {
        App::getSmarty()->assign("description", "Dodaj użytkownika");
        App::getSmarty()->display("addUserForm.tpl");
    }

    private function renderUserEditForm($userUuid) {
        App::getSmarty()->assign("user", $this->getUser($userUuid)[0]);
        App::getSmarty()->assign("description", "Edytuj użytkownika");
        App::getSmarty()->display("editUserForm.tpl");
    }
}
