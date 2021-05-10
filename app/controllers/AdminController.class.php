<?php

namespace app\controllers;

use app\services\AdminService;
use core\App;
use core\Message;
use core\ParamUtils;
use core\Validator;

// TODO implement password change functionality

class AdminController {

    private $adminService;

    function __construct() {
        $this->adminService = new AdminService();
    }

    public function action_adminDashboard() {
        $this->adminService->renderUsersTable();
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

            if ($this->adminService->validateUserData($firstName, $lastName, $email, $password, $passwordRepeat, $role)) {
                $this->adminService->addUser($firstName, $lastName, $email, $password, $role, $isActive);
                App::getMessages()->addMessage(new Message("Pomyslnie dodano użytkownika", Message::INFO));
            }
        }
        $this->adminService->renderAddUserForm();
    }

    public function action_editUser() {
        $userUuid = ParamUtils::getFromGet("user_uuid");
        $v = new Validator();

        if ($v->validateUuid($userUuid) && $this->adminService->userExist($userUuid)) {
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                $firstName = ParamUtils::getFromPost("first_name");
                $lastName = ParamUtils::getFromPost("last_name");
                $email = ParamUtils::getFromPost("email");
                $role = (ParamUtils::getFromPost("role") === "admin") ? "admin" : "user";
                $isActive = ParamUtils::getFromPost("is_active") == "true";

                if ($this->adminService->validateUserNameEmailAndRole($firstName, $lastName, $email, $role)) {
                    $this->adminService->editUser($userUuid, $firstName, $lastName, $email, $role, $isActive);
                    App::getMessages()->addMessage(new Message("Pomyślnie edytowano użytkownika", Message::INFO));
                }
            }
            $this->adminService->renderUserEditForm($userUuid);
            exit();
        }
        $this->adminService->renderUsersTable();
    }
}
