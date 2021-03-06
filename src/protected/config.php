<?php

use cri2net\php_pdo_db\PDO_DB;

$__INT_IP = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER["REMOTE_ADDR"] : '';
if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $__INT_IP = (isset($_SERVER["HTTP_X_REAL_IP"])) ? $_SERVER["HTTP_X_REAL_IP"] : $_SERVER["HTTP_X_FORWARDED_FOR"];
}
define('USER_REAL_IP', $__INT_IP);

session_name('mdl_site_session');
session_start();
@ini_set('display_errors', 0);

date_default_timezone_set("Europe/Kiev");
define('ROOT', dirname(preg_replace('/\\\\/', '/', __DIR__ . '../')));
define('PROTECTED_DIR', ROOT . '/protected');

@ini_set('error_log', PROTECTED_DIR . '/logs/php-errors.log');

if (!file_exists(ROOT . "/protected/conf/db.conf.php")) {
    die('Please create file /protected/conf/db.conf.php as copy of /protected/conf/db.conf.sample.php');
}

define('MAX_AMOUNT', 14500);

require_once(PROTECTED_DIR . "/conf/db.conf.php");
require_once(PROTECTED_DIR . "/conf/errors.php");
require_once(PROTECTED_DIR . "/conf/lang.php");
require_once(PROTECTED_DIR . "/lib/func.lib.php");
require_once(PROTECTED_DIR . "/vendor/autoload.php");

$me = UserAgent::detect();
define('HTTP_USER_AGENT', $me->user_agent);

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '';
}

switch (USER_REAL_IP) {
    case '127.0.0.1':
        define('COOKIE_DOMAIN', '.mdl.local');
        define('BASE_URL', 'http://mdl.local');
        define('SITE_DOMAIN', 'mdl.local');
        break;
    
    default:
        define('COOKIE_DOMAIN', '');
        define('BASE_URL', 'https://my.mdl.com.ua');
        define('SITE_DOMAIN', 'my.mdl.com.ua');
}

$tmp = [
    'host'     => DB_HOST,
    'user'     => DB_USER,
    'password' => DB_PASSWORD,
    'name'     => DB_NAME,
];
if (defined('DB_PORT')) {
    $tmp['port'] = DB_PORT;
}

PDO_DB::initSettings($tmp);

define('EMAIL_FROM', 'websupport@gerc.ua');
define('EMAIL_HOST', '91.200.41.117');
define('EMAIL_FROM_NAME', 'Місто для людей');
define('REMEMBER_COOKIE_NAME', '__mdludata');

Authorization::check_login();
if (Authorization::isLogin()) {
    $__userData = User::getUserById(Authorization::getLoggedUserId());
}

$router = new Routing(PROTECTED_DIR . '/conf/routing.xml');
$route_path = (strpos($_SERVER['REQUEST_URI'], '?') !== false)
    ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
    : $_SERVER['REQUEST_URI'];
$__route_result = $router->get($route_path);

$prohibided_flats = [987202, 1418852];

require_once(PROTECTED_DIR . "/headers/_all.php");
