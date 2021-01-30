<?php

/*
 * Framework initialization
 * - load config, messages, autoloader, router - prepare functions returning this global objects
 * - prepare functions loading smarty, twig, database and autoloader on demand (only once)
 * - load core functions, user roles from session and load action name to routing
 *
 *  * @author Przemysław Kudłacik
 */
require_once 'core/Config.class.php';
require_once 'core/App.class.php';
require_once  'functions.php';

use core\App;
use core\Config;
use core\SessionUtils;

$_PARAMS = array(); #global array for parameters from clean URL
$conf = new Config();

# ---- Basic URL options - rather constant
$conf->clean_urls = false;           # turn on pretty urls
$conf->action_param = 'action';     # action parameter name (not needed for clean_urls)
$conf->action_script = '/ctrl.php'; # front controller with location

include 'config.php'; //set user configuration

# ---- Helpful values generated automatically
$conf->root_path = dirname(__FILE__);
$conf->server_url = $conf->protocol.'://'.$conf->server_name;
$conf->app_url = $conf->server_url.$conf->app_root;
if ($conf->clean_urls) $conf->action_root = $conf->app_root."/"; #for clean urls
else $conf->action_root = $conf->app_root.'/index.php?'.$conf->action_param.'='; #for regular urls
$conf->action_url = $conf->server_url.$conf->action_root;
$conf->assets_url = $conf->app_url."/assets/";

// config cookies flags
$conf->cookie_http_only = true;
$conf->cookie_secure = false;
$conf->cookie_same_site = "strict";
$conf->cookie_path = $conf->app_root;
$conf->cookie_domain = $conf->server_name;
if (strtolower($conf->protocol) === "https") {
    $conf->cookie_secure = true;
}

session_set_cookie_params([
    "httponly"=>$conf->cookie_http_only,
    "secure"=>$conf->cookie_secure,
    "samesite"=>$conf->cookie_same_site,
    "path"=>$conf->cookie_path,
    "domain"=>$conf->cookie_domain
]);
session_name("sessionId");

App:: createAndInitialize($conf);

// store globally used variable go have access to them from every script
App::getSmarty()->assign("userData", SessionUtils::load("userData", true));
