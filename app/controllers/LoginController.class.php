<?php

namespace app\controllers;

use app\services\LoginService;
use core\App;
use core\Message;
use core\RoleUtils;
use core\SessionUtils;
use core\Validator;
use core\ParamUtils;

class LoginController {

    private $email;
    private $password;
    private $loginService;

    function __construct() {
        $this->loginService = new LoginService();
    }

    public function action_login() {
        if (SessionUtils::load("userData", true) != null) {
            App::getRouter()->redirectTo("dashboard");
        }
        $this->email = ParamUtils::getFromPost("email");
        $this->password = ParamUtils::getFromPost("password");
        // if request method is post and validation is okay, login user
        if ($_SERVER["REQUEST_METHOD"] === "POST" && $this->loginService->validateLogin($this->email, $this->password)) {
            $this->loginService->loginUser($this->email);
        }
        $this->loginService->renderLoginTemplate();
    }

    public function action_logout() {
        $this->loginService->logoutUser();
    }
}
