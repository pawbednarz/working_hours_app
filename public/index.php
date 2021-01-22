<?php
require_once '../init.php';
use core\App;

// TODO download needed fonts to use locally
header("Content-Security-Policy: default-src 'self' fonts.googleapis.com fonts.gstatic.com");

include App::getConf()->root_path.App::getConf()->action_script;
