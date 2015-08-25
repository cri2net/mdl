<?php

class Authorization
{
    const SALT1 = 'xBBU-?KN;Avay#1D^8sdf)8iP">@v*5=Jwym"uGggea';
    const SALT2 = '08sdfDCqZ<+SI%Yjm,qTUPHmI+#j:UZ)Z$USjI^lFg6';
    const COOKIE_SALT = '2srT57J./(*._8M__jh&~$J9lFgzxs.m75176';

    public static function check_login()
    {
        if (isset($_POST['login']) && $_POST['login']) {
            $remember = isset($_POST['remember-me']);
            $_SESSION['auth_data'] = array(
                'login' => stripslashes($_POST['email_login']),
                'password' => stripslashes($_POST['password'])
            );
            self::login($_SESSION['auth_data']['login'], $_SESSION['auth_data']['password'], false, $remember);
        } elseif (isset($_SESSION['auth_data'])) {
            self::login($_SESSION['auth_data']['login'], $_SESSION['auth_data']['password'], false, -1);
        } else {
            self::check_cookie();
        }
    }

    public static function login($login, $password, $is_hash=false, $remember=false)
    {
        $pdo = PDO_DB::getPDO();
        // $password_temp = $password;
        $login = $pdo->quote($login);

        if (!$is_hash) {
            $password = md5(md5($password));
            $password = "MD5(CONCAT(password_key, '$password'))";
        } else {
            $password = $pdo->quote($password);
        }

        $result = $pdo->query("SELECT * FROM ".DB_TBL_USERS." WHERE `deleted`=0 AND `email`=$login AND `password` = $password LIMIT 1");
        $arr = $result->fetch();
    
        if (empty($arr)) {
            setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
            unset($_SESSION['auth'], $_SESSION['auth_data']);
        } else {
            $_SESSION['auth'] = $arr;

            // чтоб не сбивать работу куки при "логине" в рамке работы сессии
            if ($remember !== -1) {
                if ($remember) {
                    setcookie(REMEMBER_COOKIE_NAME, md5(md5($arr['id']) . self::COOKIE_SALT), time() + 60 * 60 * 24 * 60, "/", COOKIE_DOMAIN);
                } else {
                    setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
                }
            }
        }

        return;
    }

    public static function generate_db_password($password, $password_key)
    {
        return md5($password_key . md5(md5($password)));
    }

    public static function get_auth_hash1($user_id)
    {
        $user_id = (int)$user_id;
        $userData = PDO_DB::row_by_id(DB_TBL_USERS, $user_id);
        return md5($userData['email'] . self::SALT1);
    }
    
    public static function get_auth_hash2($user_id, $hash1)
    {
        return md5($hash1 . self::SALT2 . md5($user_id . self::SALT1 . $hash1));
    }
    
    public static function encode_auth_uid_hash($user_id)
    {
        return 'u' . $user_id;
    }
    
    public static function decode_auth_uid_hash($uid_hash)
    {
        return ((int)substr($uid_hash, 1));
    }
    
    public static function check_cookie()
    {
        if ((isset($_COOKIE[REMEMBER_COOKIE_NAME])) && ($_GET['page'] != 'logout')) {
            $pdo = PDO_DB::getPDO();
            $cookie = $pdo->quote($_COOKIE[REMEMBER_COOKIE_NAME]);
            $salt = $pdo->quote(self::COOKIE_SALT);
            $result = PDO_DB::query("SELECT * FROM ".DB_TBL_USERS." WHERE MD5(CONCAT(MD5(`id`), $salt)) = $cookie LIMIT 1");
            $user = $result->fetch();

            if ($user !== false) {
                self::login($user['email'], $user['password'], true, true);
            }
        }

        if (!self::isLogin()) {
            if (isset($_REQUEST['hash2']) && isset($_REQUEST['uid'])) {
                $uid = self::decode_auth_uid_hash($_REQUEST['uid']);
                if (strcasecmp($_REQUEST['hash2'], self::get_auth_hash2($uid)) == 0) {
                    $user = PDO_DB::row_by_id(DB_TBL_USERS, $uid);
                    self::login($user['email'], $user['password'], true, true);
                }
            }
        }
    }
    
    public static function getLoggedUserId()
    {
        if (self::isLogin()) {
            return $_SESSION['auth']['id'];
        }
        return false;
    }

    public static function isLogin()
    {
        session_start();
        return (!empty($_SESSION['auth']['id']));
    }
    
    public static function logout()
    {
        unset($_SESSION['auth'], $_SESSION['auth_data']);
        session_destroy();
        setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
        header("Location: " . BASE_URL . "/");
        exit();
    }
}
