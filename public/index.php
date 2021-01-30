<?php
require_once '../init.php';
use core\App;

// TODO download needed fonts to use locally
header("Content-Security-Policy: default-src 'self' fonts.googleapis.com fonts.gstatic.com; object-src 'none'; frame-ancestors 'none'");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: origin");
if (strtolower(App::getConf()->protocol) === "https") {
    header("Strict-Transport-Security: max-age=63072000; includeSubDomains;");
}

include App::getConf()->root_path.App::getConf()->action_script;
