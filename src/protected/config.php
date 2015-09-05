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
            break;
        
        default:
            switch ($_SERVER['HTTP_HOST']) {
                case 's1.mailing.com.ua':
                    $_SERVER['REQUEST_URI'] = str_replace('/clients/gioc/', '/', $_SERVER['REQUEST_URI']);
                    define('COOKIE_DOMAIN', '.' . $_SERVER['HTTP_HOST']);
                    define('BASE_URL', "http://{$_SERVER['HTTP_HOST']}/clients/gioc");
                    break;

                default:
                    define('COOKIE_DOMAIN', '.splata.gioc.kiev.ua');
                    define('BASE_URL', 'http://splata.gioc.kiev.ua');
            }
    }

    define('EMAIL_FROM', 'info@splata.gioc.kiev.ua');
    define('EMAIL_FROM_NAME', 'КП «ГіОЦ»');
    define('SITE_DOMAIN', 'splata.gioc.kiev.ua');
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



    // $static_page_arr = array(
    //     // это "Перелік розробок"
    //     '12' => 155, '23' => 156, '32' => 157, '33' => 158, '34' => 159, '35' => 160, '36' => 161, '37' => 162, '28' => 163, '27' => 164, '25' => 165, '30' => 166, '26' => 167, '29' => 168, '216' => 169, '19' => 170, '20' => 171, '21' => 172, '22' => 173, '10' => 174, '17' => 175, '18' => 176, '15' => 177, '14' => 178, '215' => 179, '214' => 180,
    // );
    //     $text = file_get_contents('C:\www\gioc\a.txt');

    //     foreach ($static_page_arr as $key => $value) {
    //         $text = str_replace("http://gioc.kiev.ua/main/document/$key/", StaticPage::getPath($value), $text);
    //     }

    //     die($text);












    // $arr = array(12, 23, 32, 33, 34, 35, 36, 37, 28, 27, 25, 30, 26, 29, 216, 11, 19, 20, 21, 22, 10, 17, 18, 15, 14, 16, 215, 214 );
    // $pos = 0;
    // $result = array();

    // $ok = false; 
    // for ($i=0; $i < count($arr); $i++) { 
    //     $news = PDO_DB::table_list('gioc_news', "old_site_id = {$arr[$i]}", null, "1");
    //     if (count($news) == 0) {
    //         continue;
    //     }

    //     foreach ($news as $item) {
    //         $_key = trim(composeUrlKey($item['title']), '-');
            
    //         if ($_key == 'programniy-kompleks-oblik-zarobitnoyi-plati') {
    //             if ($ok) {
    //                 $_key = 'education-programniy-kompleks-oblik-zarobitnoyi-plati';
    //             } else {
    //                 $ok = true;
    //             }
    //         }
            
    //         $pos++;
    //         $insert = array(
    //             'idp' => 13,
    //             'pos' => $pos,
    //             'key' => $_key,
    //             'h1' => $item['title'],
    //             'breadcrumb' => $item['title'],
    //             'created_at' => $item['created_at'],
    //             'updated_at' => $item['updated_at'],
    //             'announce' => $item['announce'],
    //             'text' => $item['text'],
    //             'seo_title' => $item['seo_title']
    //         );

    //         // $_id = PDO_DB::insert($insert, 'gioc_pages');

    //         $result['main_'.$arr[$i]] = $_id;
    //     }
    // }

    // print_r($result);

    // die('ok');




