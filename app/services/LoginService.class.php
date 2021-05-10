<?php

namespace app\services;

use core\App;
use core\Message;
use core\RoleUtils;
use core\SessionUtils;
use core\Validator;

class LoginService {

    public function validateLogin($email, $password) {
        // check if required data is provided - if not - exit function
        if (empty($email)) {
            App::getMessages()->addMessage(new Message("Email nie może być pusty", Message::INFO));
        }

        if (empty($password)) {
            App::getMessages()->addMessage(new Message("Hasło nie może być puste", Message::INFO));
        }

        // if there are errors exit function and return false
        if (!App::getMessages()->isEmpty()) return false;

        $v = new Validator();

        // validate email address
        $email = $v->validate($email, [
            "email"=>true,
            "required"=>true,
            "trim"=>true,
            "validator_message"=>"Niepoprawny format adresu email"
        ]);

        if (!$v->isLastOK()) {
            // call pbkdf2 function to prevent time-based user enumaration attacks attacks
            $this->checkPassword("password", "");
            return false;
        }

        $isPasswordCorrect = false;
        $isActive = false;
        // try to get user from database by email
        try {
            $userData = App::getDB()->select("user", [
                "email",
                "password",
                "is_active"
            ],[
                "email"=>$email
            ]);

            // id user with provided email exist, check if password matches and return result
            // else call pbkdf2
            if (count($userData) == 1) {
                $isPasswordCorrect = $this->checkPassword($password, $userData[0]["password"]);
                $isActive = $userData[0]["is_active"];
            } else {
                // call pbkdf2 function to prevent time-based user enumaration attacks attacks
                $this->checkPassword("password", "");
            }

        } catch (\PDOException $e) {
            // TODO write to logs
            // echo $e;
            App::getMessages()->addMessage("Wystąpił błąd podczas logowania użytkownika. Spróbuj ponownie, lub skontaktuj się z administratorem systemu");
        }
        if (!$isPasswordCorrect) {
            App::getMessages()->addMessage(new Message("Niepoprawny email lub hasło", Message::ERROR));
            return false;
        }

        if (!$isActive) {
            App::getMessages()->addMessage(new Message("Użytkownik jest nieaktywny. Skontaktuj się z administratorem", Message::INFO));
            return false;
        }
        return true;
    }

    private function checkPassword($formPassword, $dbPassword) {
        return hash_pbkdf2("sha512", $formPassword, "", 15000) == $dbPassword;
    }

    public function loginUser($email) {
        $data = array();
        try {
            $data = App::getDB()->select("user", [
                "uuid",
                "first_name",
                "last_name",
                "role"
            ],[
                "email"=>$email
            ]);
            $data = $data[0];
        } catch (\PDOException $e) {
            // TODO write to logs
            // echo $e
            App::getMessages()->addMessage("Wystąpił błąd podczas logowania użytkownika. Spróbuj ponownie, lub skontaktuj się z administratorem systemu");
        }
        RoleUtils::addRole($data["role"]);
        SessionUtils::store("userUuid", $data["uuid"]);
        // create userData object to store data of user in there
        $userData = new \stdClass();
        $userData->firstName = $data["first_name"];
        $userData->lastName = $data["last_name"];
        SessionUtils::store("userData", $userData);
        App::getRouter()->redirectTo("dashboard");
    }

    public function logoutUser() {
        session_destroy();
        App::getRouter()->redirectTo("login");
    }

    public function renderLoginTemplate() {
        App::getSmarty()->display("login.tpl");
    }


}
