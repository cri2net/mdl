<?php

class Authorization
{
    const USER_CODES_TABLE = DB_TBL_USER_CODES;

    public static function cron()
    {
        // выключаем просроченные запросы на сброс пароля.
        // По идее, они будут нормально работать и без этого.
        $time = microtime(true);
        PDO_DB::updateWithWhere(['is_active' => 0], self::USER_CODES_TABLE, "is_active=1 AND expires_at<$time");
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
        // для поддержки пользователей personal-account, в этом методе есть sha1.
        // personal-account дал только sha1 паролей своих пользователей.
        // так что, при успешной авторизации, мы меняем им хеш пароля на наш.
        // отличительная черта таких пользователей - пустое поле password_key
        
        $column = self::getLoginColumn($login);
        $table = User::TABLE;

        $_SESSION['auth_data'] = [
            'login' => $login,
            'password' => $password,
            'is_hash' => $is_hash,
            'column' => $column
        ];

        $pdo = PDO_DB::getPDO();
        $login = $pdo->quote($login);

        if (!$is_hash) {
            $sha1_password = sha1($password);
            $raw_password = $password;

            $password = md5(md5($password));
            $password = "MD5(CONCAT(password_key, '$password'))";
        } else {
            $sha1_password = $password;
            $password = $pdo->quote($password);
        }

        $result = $pdo->query("SELECT * FROM $table
                                WHERE deleted=0
                                      AND $column=$login
                                      AND (
                                            `password` = $password
                                            OR (`password` = '$sha1_password' AND password_key='')
                                        )
                                LIMIT 1");
        $arr = $result->fetch();

        if (empty($arr)) {
            setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
            unset($_SESSION['auth'], $_SESSION['auth_data']);
        } else {

            // это человек от personal-account. Меняем ему данные под нашу схему авторизации
            if (empty($arr['password_key']) && !$is_hash) {
                $password_key = generateCode();
                $update = [
                    'password' => self::generate_db_password($raw_password, $password_key),
                    'password_key' => $password_key
                ];
                PDO_DB::update($update, User::TABLE, $arr['id']);

                // сразу обновляем данные, чтоб они были актуальны
                $arr['password'] = $update['password'];
                $arr['password_key'] = $update['password_key'];
            }

            $_SESSION['auth'] = $arr;

            // чтоб не сбивать работу куки при "логине" в рамке работы сессии
            if ($remember !== -1) {
                if ($remember) {
                    setcookie(REMEMBER_COOKIE_NAME, md5(md5($arr['id']) . $arr['password'] . $arr['password_key']), time() + 86400 * 60, "/", COOKIE_DOMAIN);
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

        // если это текущий авторизованный пользователь, незачем делать запрос в базу
        if (isset($_SESSION['auth']['id']) && (intval($_SESSION['auth']['id']) === $user_id)) {
            $userData = $_SESSION['auth'];
        } else {
            $userData = PDO_DB::row_by_id(User::TABLE, $user_id);
        }
        
        // получается, что если человек сменил пароль, то все такие ссылки перестают работать. Жёстко, но секьюрно.
        return md5($userData['password'] . $userData['password_key'] . $userData['id']);
    }
    
    public static function get_auth_hash2($user_id, $hash1)
    {
        $user_id = (int)$user_id;

        // если это текущий авторизованный пользователь, незачем делать запрос в базу
        if (isset($_SESSION['auth']['id']) && (intval($_SESSION['auth']['id']) === $user_id)) {
            $userData = $_SESSION['auth'];
        } else {
            $userData = PDO_DB::row_by_id(User::TABLE, $user_id);
        }

        return md5($hash1 . $userData['password_key'] . sha1($user_id . $userData['password'] . $hash1));
    }
    
    public static function check_cookie()
    {
        if (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $pdo = PDO_DB::getPDO();
            $cookie = $pdo->quote($_COOKIE[REMEMBER_COOKIE_NAME]);
            $result = PDO_DB::query("SELECT * FROM ".User::TABLE." WHERE MD5(CONCAT(CONCAT(MD5(id), `password`), password_key)) = $cookie LIMIT 1");
            $user = $result->fetch();

            if ($user !== false) {
                self::login($user['email'], $user['password'], true, true);
            }
        }

        if (!self::isLogin()) {
            if (isset($_REQUEST['hash2']) && isset($_REQUEST['uid'])) {
                $hash1 = self::get_auth_hash1($_REQUEST['uid']);
                if (strcasecmp($_REQUEST['hash2'], self::get_auth_hash2($_REQUEST['uid'], $hash1)) == 0) {
                    $user = PDO_DB::row_by_id(User::TABLE, $_REQUEST['uid']);
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
        return (!empty($_SESSION['auth']['id']));
    }
    
    public static function logout()
    {
        unset($_SESSION['auth'], $_SESSION['auth_data']);
        @session_unset();
        @session_destroy();
        setcookie(REMEMBER_COOKIE_NAME, '', time(), "/", COOKIE_DOMAIN);
    }

    /**
     * Генерация кода для восстановления пароля
     * @param  integer          $user_id ID пользователя, для которого работает ссылка
     * @param  string           $type    тип кода. OPTIONAL
     * @param  integer | double $expires Смещение по времени от этого момента, в течение которого код работает. OPTIONAL
     * @return string           код для восстановления пароля
     */
    public static function generateUserCode($user_id, $type = 'restore', $expires = 86400)
    {
        $time = microtime(true);
        $code = generateCode(40);
        $user_id = (int)$user_id;
        $user = User::getUserById($user_id);
        
        // выключаем все предыдущие запросы
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("UPDATE " . self::USER_CODES_TABLE . " SET is_active=0 WHERE user_id=? AND is_active=1 AND type=?");
        $stm->execute([$user_id, $type]);

        $arr = [
            'user_id'                      => $user_id,
            'code'                         => $code,
            'type'                         => $type,
            'email'                        => $user['email'],
            'created_at'                   => $time,
            'expires_at'                   => $time + $expires,
            'created_by_ip'                => USER_REAL_IP,
            'created_by_user_agent_string' => HTTP_USER_AGENT,
        ];
        PDO_DB::insert($arr, self::USER_CODES_TABLE);

        return $code;
    }

    /**
     * Проверка кода доступа.
     * @param  string  $code  код для восстановления пароля
     * @param  string  $type  тип кода. OPTIONAL
     * @return array | boolean
     */
    public static function verifyUserCode($code, $type = 'restore')
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT * FROM " . self::USER_CODES_TABLE . " WHERE code=? AND type=? LIMIT 1");
        $stm->execute([$code, $type]);

        $record = $stm->fetch();

        if ($record === false) {
            throw new Exception(ERROR_GET_RESTORE_CODE);
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
    public static function unsetUserCode($id)
    {
        $arr = [
            'is_active' => 0,
            'used_at' => microtime(true),
            'used_at_ip' => USER_REAL_IP,
            'used_at_user_agent_string' => HTTP_USER_AGENT,
        ];
        PDO_DB::update($arr, self::USER_CODES_TABLE, $id);
    }
}
