<?php
    $__INT_IP = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER["REMOTE_ADDR"] : '';
    if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $__INT_IP = (isset($_SERVER["HTTP_X_REAL_IP"])) ? $_SERVER["HTTP_X_REAL_IP"] : $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    define('USER_REAL_IP', $__INT_IP);
    define('HTTP_USER_AGENT', (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : ((isset($GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'])) ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'] : ''));

    session_name('site_session');
    session_start();

    date_default_timezone_set("Europe/Kiev");
    define('ROOT', dirname(preg_replace('/\\\\/', '/', __DIR__ . '../')));

    @ini_set('error_log', ROOT . '/protected/logs/php-errors.log');



    require_once(ROOT . "/protected/conf/db.conf.php");
    require_once(ROOT . "/protected/conf/errors.php");
    require_once(ROOT . "/protected/conf/lang.php");
    require_once(ROOT . "/protected/lib/func.lib.php");
    require_once(ROOT . "/protected/vendor/autoload.php");

    switch (USER_REAL_IP) {
        case '127.0.0.1':
            define('COOKIE_DOMAIN', '.gioc.dev');
            define('BASE_URL', 'http://gioc.dev');
            define('HAVE_ACCESS_TO_API', false);
            break;
        
        default:
            switch ($_SERVER['HTTP_HOST']) {
                case 's1.mailing.com.ua':
                    $_SERVER['REQUEST_URI'] = str_replace('/clients/gioc/', '/', $_SERVER['REQUEST_URI']);
                    define('COOKIE_DOMAIN', '.' . $_SERVER['HTTP_HOST']);
                    define('BASE_URL', "http://{$_SERVER['HTTP_HOST']}/clients/gioc");
                    define('HAVE_ACCESS_TO_API', false);
                    break;

                case 'kiev.gerc.ua':
                    define('COOKIE_DOMAIN', '.' . $_SERVER['HTTP_HOST']);
                    define('BASE_URL', "http://{$_SERVER['HTTP_HOST']}");
                    define('HAVE_ACCESS_TO_API', true);
                    break;

                default:
                    define('COOKIE_DOMAIN', 'kiev.gerc.ua');
                    // define('BASE_URL', 'http://kiev.gerc.ua');
                    define('BASE_URL', 'http://splata.gioc.kiev.ua');
                    define('HAVE_ACCESS_TO_API', true);
            }
    }

    define('EMAIL_FROM', 'info@gioc.kiev.ua');
    define('EMAIL_FROM_NAME', 'КП «ГіОЦ»');
    define('SITE_NAME', 'КП «ГіОЦ»'); // for rss, etc
    define('SITE_DESCRIPTION', 'Головний iнформацiйно-обчислювальний центр'); // for rss, etc
    define('SITE_DOMAIN', 'gioc.kiev.ua');
    define('REMEMBER_COOKIE_NAME', '__giocudata');

    Authorization::check_login();
    if (Authorization::isLogin()) {
        $__userData = User::getUserById(Authorization::getLoggedUserId());
    }

    $router = new Routing(ROOT . '/protected/conf/routing.xml', true);
    $route_path = (strpos($_SERVER['REQUEST_URI'], '?') !== false)
        ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
        : $_SERVER['REQUEST_URI'];
    $__route_result = $router->get($route_path);

    require_once(ROOT . "/protected/headers/location.php");
    require_once(ROOT . "/protected/headers/x-frame-options.php");
    // require_once(ROOT."/protected/headers/content-security-policy.php");

