<?php
$__INT_IP = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER["REMOTE_ADDR"] : '';
if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    $__INT_IP = (isset($_SERVER["HTTP_X_REAL_IP"])) ? $_SERVER["HTTP_X_REAL_IP"] : $_SERVER["HTTP_X_FORWARDED_FOR"];
}
define('USER_REAL_IP', $__INT_IP);
define('HTTP_USER_AGENT', (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : ((isset($GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'])) ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'] : ''));

session_name('site_session');
session_start();

date_default_timezone_set("Europe/Kiev");
define('ROOT', dirname(preg_replace('/\\\\/', '/', __DIR__ . '../')));
define('PROTECTED_DIR', ROOT . '/protected');

@ini_set('error_log', PROTECTED_DIR . '/logs/php-errors.log');

if (!file_exists(ROOT . "/protected/conf/db.conf.php")) {
    die('Please create file /protected/conf/db.conf.php as copy of /protected/conf/db.conf.sample.php');
}

require_once(PROTECTED_DIR . "/conf/db.conf.php");
require_once(PROTECTED_DIR . "/conf/errors.php");
require_once(PROTECTED_DIR . "/conf/lang.php");
require_once(PROTECTED_DIR . "/lib/func.lib.php");
require_once(PROTECTED_DIR . "/vendor/autoload.php");

switch (USER_REAL_IP) {
    case '127.0.0.1':
        define('COOKIE_DOMAIN', '.cks.dev');
        define('BASE_URL', 'http://frame.cks.dev');
        break;
    
    default:
        if (!isset($_SERVER['HTTP_HOST']) || !$_SERVER['HTTP_HOST']) {
            $_SERVER['HTTP_HOST'] = 'www.gerc.ua';
        }

        define('COOKIE_DOMAIN', '.gerc.ua');
        if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        }
        define('BASE_URL', $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://' . $_SERVER['HTTP_HOST'] . '/cks');

        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 4);
}

define('EMAIL_FROM', 'no-reply@cks.kiev.ua');
define('EMAIL_TO', 'zvernennya@src.kiev.ua');
define('EMAIL_HOST', '91.200.41.117');
define('EMAIL_FROM_NAME', 'КК ЦКС');
define('SITE_DOMAIN', 'cks.kiev.ua');
define('REMEMBER_COOKIE_NAME', '__cksudata');

Authorization::check_login();
if (Authorization::isLogin()) {
    $__userData = User::getUserById(Authorization::getLoggedUserId());
}

$router = new Routing(PROTECTED_DIR . '/conf/routing.xml');
$route_path = (strpos($_SERVER['REQUEST_URI'], '?') !== false)
    ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
    : $_SERVER['REQUEST_URI'];
$__route_result = $router->get($route_path);

require_once(PROTECTED_DIR . "/headers/location.php");
require_once(PROTECTED_DIR . "/headers/x-frame-options.php");
// require_once(PROTECTED_DIR."/headers/content-security-policy.php");
