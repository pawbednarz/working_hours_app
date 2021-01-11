<?php

namespace app\controllers;

use core\App;

class UserController {

    public function action_addUser() {
        $this->addUser("Maciej", "Testowy", "test@test.com", "pass", "user");
    }

    public function action_editUser() {

    }

    public function action_deleteUser() {

    }

    public function addUser($firstName, $lastName, $email, $password, $role) {
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
}
