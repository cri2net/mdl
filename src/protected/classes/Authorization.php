<?php

class Authorization
{
    const SALT1 = 'xBBU-?KN;Avay#1D^8sdf)8iP">@v*5=Jwym"uGggea';
    const SALT2 = '08sdfDCqZ<+SI%Yjm,qTUPHmI+#j:UZ)Z$USjI^lFg6';
    const COOKIE_SALT = '2srT57J./(*._8M__jh&~$J9lFgzxs.m75176';
    const RESTORE_TABLE = DB_TBL_USER_RESTORE;

    public static function cron()
    {
        // выключаем просроченные запросы на сброс пароля.
        // По идее, они будут нормально работать и без этого.
        $time = microtime(true);
        PDO_DB::updateWithWhere(['is_active' => 0], self::RESTORE_TABLE, "is_active=1 AND expires_at<$time");
    }

    public static function check_login()
    {
        if (isset($_SESSION['auth_data'])) {
            self::login($_SESSION['auth_data']['login'], $_SESSION['auth_data']['password'], $_SESSION['auth_data']['is_hash'], -1);
        } else {
            self::check_cookie();
        }
    }

    public static function getLoginColumn($login)
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        if (strpos($login, '+') === 0) {
            return 'mob_phone';
        }
        return 'login';
    }

    public static function login($login, $password, $is_hash = false, $remember = false)
    {
        $column = self::getLoginColumn($login);

        $_SESSION['auth_data'] = [
            'login' => $login,
            'password' => $password,
            'is_hash' => $is_hash,
            'column' => $column
        ];

        $pdo = PDO_DB::getPDO();
        $login = $pdo->quote($login);

        if (!$is_hash) {
            $password = md5(md5($password));
            $password = "MD5(CONCAT(password_key, '$password'))";
        } else {
            $password = $pdo->quote($password);
        }

        $result = $pdo->query("SELECT * FROM ".DB_TBL_USERS." WHERE deleted=0 AND $column=$login AND `password` = $password LIMIT 1");
        $arr = $result->fetch();

        if (empty($arr)) {
            setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
            unset($_SESSION['auth'], $_SESSION['auth_data']);
        } else {
            $_SESSION['auth'] = $arr;

            // чтоб не сбивать работу куки при "логине" в рамке работы сессии
            if ($remember !== -1) {
                if ($remember) {
                    setcookie(REMEMBER_COOKIE_NAME, md5(md5($arr['id']) . $arr['password'] . self::COOKIE_SALT), time() + 86400 * 60, "/", COOKIE_DOMAIN);
                } else {
                    setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
                }
            }
        }
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
            $result = PDO_DB::query("SELECT * FROM ".DB_TBL_USERS." WHERE MD5(CONCAT(CONCAT(MD5(`id`), `password`), $salt)) = $cookie LIMIT 1");
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
    }

    /**
     * Генерация кода для восстановления пароля
     * @param  integer          $user_id ID пользователя, для которого работает ссылка
     * @param  integer | double $expires Смещение по времени от этого момента, в течение которого код работает. OPTIONAL
     * @return string           код для восстановления пароля
     */
    public static function generateRestoreCode($user_id, $expires = 86400)
    {
        $time = microtime(true);
        $code = generateCode(40);
        $user_id = (int)$user_id;
        $user = User::getUserById($user_id);

        // выключаем все предыдущие запросы
        PDO_DB::updateWithWhere(['is_active' => 0], self::RESTORE_TABLE, "user_id=$user_id AND is_active=1");

        $arr = [
            'user_id'                      => $user_id,
            'code'                         => $code,
            'email'                        => $user['email'],
            'created_at'                   => $time,
            'expires_at'                   => $time + $expires,
            'created_by_ip'                => USER_REAL_IP,
            'created_by_user_agent_string' => HTTP_USER_AGENT,
        ];
        PDO_DB::insert($arr, self::RESTORE_TABLE);

        return $code;
    }

    /**
     * Проверка кода доступа.
     * @param  string  $code  код для восстановления пароля
     * @return array | boolean
     */
    public static function verifyRestoreCode($code)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT * FROM " . self::RESTORE_TABLE . " WHERE code=? LIMIT 1");
        $stm->execute([$code]);

        $record = $stm->fetch();

        if ($record === false) {
            throw new Exception(ERROR_GER_RESTORE_CODE);
            return false;
        }

        if ($record['expires_at'] < microtime(true)) {
            throw new Exception(ERROR_RESTORE_CODE_EXPIRE);
            return false;
        }

        if (!$record['is_active']) {
            throw new Exception(ERROR_RESTORE_CODE_ACTIVE);
            return false;
        }

        return $record;
    }

    /**
     * Код использован. Выключаем его.
     * @param  integer $id  ID кода
     * @return void
     */
    public static function unsetRestoreCode($id)
    {
        $arr = [
            'is_active' => 0,
            'used_at' => microtime(true),
            'used_at_ip' => USER_REAL_IP,
            'used_at_user_agent_string' => HTTP_USER_AGENT,
        ];
        PDO_DB::update($arr, self::RESTORE_TABLE, $id);
    }
}
