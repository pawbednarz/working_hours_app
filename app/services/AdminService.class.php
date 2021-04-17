<?php

namespace app\services;

use core\App;
use core\Message;
use core\Validator;

class AdminService {
    private function getUsers() {
        return App::getDB()->select("user", "*");
    }

    private function getUser($userUuid) {
        $data = App::getDB()->select("user", [
            "uuid",
            "first_name",
            "last_name",
            "email",
            "role",
            "is_active"
        ], [
            "uuid"=>$userUuid
        ]);
        return $data[0];
    }

    public function addUser($firstName, $lastName, $email, $password, $role, $isActive) {
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

    public function editUser($uuid, $firstName, $lastName, $email, $role, $isActive) {
        App::getDB()->update("user", [
            "first_name"=>$firstName,
            "last_name"=>$lastName,
            "email"=>$email,
            "role"=>$role,
            "is_active"=>$isActive
        ], [
            "uuid"=>$uuid
        ]);
    }

    public function userExist($userUuid) {
        $result = App::getDB()->has("user", [
            "uuid"=>$userUuid
        ]);
        if (!$result) {
            App::getMessages()->addMessage(new Message("Użytkownik o podanym UUID nie istnieje", Message::ERROR));
        }
        return $result;
    }

    private function userExistByEmail($email) {
        $result = App::getDB()->has("user", [
            "email"=>$email
        ]);
        if ($result) {
            App::getMessages()->addMessage(new Message("Podany email jest już zajęty", Message::ERROR));
        }
        return $result;
    }

    public function validateUserData(&$firstName, &$lastName, &$email, &$password, $passwordRepeat, $role) {
        if ($password != $passwordRepeat) {
            App::getMessages()->addMessage(new Message("Wprowadzone hasła nie są takie same", Message::ERROR));
            return false;
        }

        if ($this->userExistByEmail($email)) {
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

    public function validateUserNameEmailAndRole(&$firstName, &$lastName, &$email, $role) {
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

    public function renderUsersTable() {
        App::getSmarty()->assign("description", "Panel administratora");
        App::getSmarty()->assign("users", $this->getUsers());
        App::getSmarty()->display("usersTable.tpl");
    }

    public function renderAddUserForm() {
        App::getSmarty()->assign("description", "Dodaj użytkownika");
        App::getSmarty()->display("addUserForm.tpl");
    }

    public function renderUserEditForm($userUuid) {
        App::getSmarty()->assign("user", $this->getUser($userUuid));
        App::getSmarty()->assign("description", "Edytuj użytkownika");
        App::getSmarty()->display("editUserForm.tpl");
    }
}
