<?php

namespace app\controllers;

use core\SessionUtils;

class NowyCtrl{

    public function  action_test() {
        echo $_SESSION["first_name"];
        echo "test";
    }
}
