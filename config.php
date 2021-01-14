<?php
$conf->debug = false; # set true during development and use in your code (for instance check if true to send additional message)

if ($conf->debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}


# ---- Webapp location
$conf->server_name = '192.168.0.108';   # server address and port
$conf->protocol = 'http';           # http or https
$conf->app_root = '/working_hours_app/public';   # project subfolder in domain (relative to main domain)

# ---- Database config - values required by Medoo
$conf->db_type = 'mysql';
$conf->db_server = '192.168.0.108';
$conf->db_name = 'working_hours';
$conf->db_user = 'working_hours';
$conf->db_pass = 'working_hours';
$conf->db_charset = 'utf8';

# ---- Database config - optional values
$conf->db_port = '3306';
#$conf->db_prefix = '';
$conf->db_option = [ PDO::ATTR_CASE => PDO::CASE_NATURAL, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
