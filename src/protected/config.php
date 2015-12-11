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

    @ini_set('error_log', ROOT . '/protected/logs/php-errors.log');

    if (!file_exists(ROOT . "/protected/conf/db.conf.php")) {
        die('Please create file /protected/conf/db.conf.php as copy of /protected/conf/db.conf.sample.php');
    }

    require_once(ROOT . "/protected/conf/db.conf.php");
    require_once(ROOT . "/protected/conf/errors.php");
    require_once(ROOT . "/protected/conf/lang.php");
    require_once(ROOT . "/protected/lib/func.lib.php");
    require_once(ROOT . "/protected/vendor/autoload.php");
    require_once(ROOT . "/protected/conf/browser.php");
    
    switch (USER_REAL_IP) {
        case '127.0.0.1':
            define('COOKIE_DOMAIN', '.gioc.dev');
            define('BASE_URL', 'http://gioc.dev');
            define('HAVE_ACCESS_TO_API', true);
            break;
        
        default:
            if (!isset($_SERVER['HTTP_HOST']) || !$_SERVER['HTTP_HOST']) {
                $_SERVER['HTTP_HOST'] = 'www.gioc.kiev.ua';
            }

            define('COOKIE_DOMAIN', '.gioc.kiev.ua');
            define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST']);
            define('HAVE_ACCESS_TO_API', true);
    }

    define('EMAIL_FROM', 'info@gioc.kiev.ua');
    define('EMAIL_HOST', 'gioc.kiev.ua');
    define('EMAIL_FROM_NAME', 'КП «ГіОЦ»');
    define('SITE_NAME', 'КП «ГіОЦ»'); // for rss, etc
    define('SITE_DESCRIPTION', 'Головний iнформацiйно-обчислювальний центр'); // for rss, etc
    define('SITE_DOMAIN', 'gioc.kiev.ua');
    define('REMEMBER_COOKIE_NAME', '__giocudata');

    Authorization::check_login();
    if (Authorization::isLogin()) {
        $__userData = User::getUserById(Authorization::getLoggedUserId());
    }

    $router = new Routing(ROOT . '/protected/conf/routing.xml');
    $route_path = (strpos($_SERVER['REQUEST_URI'], '?') !== false)
        ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
        : $_SERVER['REQUEST_URI'];
    $__route_result = $router->get($route_path);

    require_once(ROOT . "/protected/headers/location.php");
    require_once(ROOT . "/protected/headers/x-frame-options.php");
    // require_once(ROOT."/protected/headers/content-security-policy.php");


// $pdo = PDO_DB::getPDO();
// $stm_del = $pdo->prepare("DELETE FROM ta WHERE id=? LIMIT 1");
// $stm = $pdo->query("SELECT * FROM ta WHERE id > 5000");
// while ($row = $stm->fetch()) {
    



//     $obj_id = Flat::getFlatByPlatCode($row['int8'])['object_id'];
//     var_dump($obj_id);
// die('s');
//     if (!$obj_id) {
//         continue;
//     }

//     $arr = [
//         'city_id' => 100,
//         'code' => $row['authkey'],
//         'plat_code' => $row['int8'],
//         'created_at' => microtime(true),
//         'object_id' => $obj_id
//     ];
//     var_dump($arr); die();
//     PDO_DB::insert($arr, 'gioc_auth_code', true);
//     $stm_del->execute([$row['id']]);
// }

// die();

