<?php

class User
{
    const TABLE = DB_TBL_USERS;

    public static function getUserIdByEmail($email)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT id FROM ". DB_TBL_USERS ." WHERE email=? AND `deleted`=0 LIMIT 1");
        $stm->execute(array($email));
        $column = $stm->fetchColumn();
        if ($column === false) {
            return null;
        }
        return $column;
    }

    public static function getUserIdByPhone($phone)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT id FROM ". DB_TBL_USERS ." WHERE mob_phone=? AND `deleted`=0 LIMIT 1");
        $stm->execute(array($phone));
        $column = $stm->fetchColumn();
        if ($column === false) {
            return null;
        }
        return $column;
    }

    /**
     * Удаляем пользователя из системы
     * @param  string  $comment - примечание пользователя. OPTIONAL
     * @param  integer $user_id - id пользователя, которого удаляем. По умолчанию - текущий авторизованный. OPTIONAL
     * @return void
     */
    public static function delete($comment = '', $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
            if (!$user_id) {
                throw new Exception(ERROR_USER_NOT_LOGGED_IN);
                return false;
            }
        }

        PDO_DB::update(
            ['deleted' => 1, 'deleted_message' => $comment, 'deleted_timestamp' => microtime(true)],
            self::TABLE,
            $user_id
        );
    }

    public static function getUserByEmail($email)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT * FROM ". DB_TBL_USERS ." WHERE email=? AND `deleted`=0 LIMIT 1");
        $stm->execute(array($email));
        $user = $stm->fetch();
        if ($user === false) {
            return null;
        }
        return $user;
    }

    public static function getUserById($user_id)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT * FROM ". DB_TBL_USERS ." WHERE id=? AND `deleted`=0 LIMIT 1");
        $stm->execute(array($user_id));
        $user = $stm->fetch();
        if ($user === false) {
            return null;
        }
        return $user;
    }

    public static function registration($data)
    {
        $password_key = generateCode();

        $data = array(
            'email' => $data['email'],
            'password' => Authorization::generate_db_password($data['password'], $password_key),
            'password_key' => $password_key,
            'lastname' => $data['lastname'],
            'name' => $data['name'],
            'fathername' => $data['fathername'],
            'reg_time' => microtime(true),
            'mob_phone' => $data['phone']
        );

        $user_id = PDO_DB::insert($data, self::TABLE);
        self::sendRegLetter($user_id, $password, 'registration');
        return $user_id;
    }

    public static function registerFromPayment($email, $lastname, $name, $fathername)
    {
        $password_key = generateCode();
        $password = generateCode();

        $data = array(
            'email' => $email,
            'password' => Authorization::generate_db_password($password, $password_key),
            'password_key' => $password_key,
            'lastname' => $lastname,
            'name' => $name,
            'fathername' => $fathername,
            'reg_time' => microtime(true),
            'mob_phone' => '',
            'auto_reg' => 1,
        );

        $user_id = PDO_DB::insert($data, self::TABLE);
        self::sendRegLetter($user_id, $password, 'auto_reg_letter');
        return $user_id;
    }

    public static function sendRegLetter($user_id, $password, $template)
    {
        $user_id = (int)$user_id;
        $user = PDO_DB::row_by_id(self::TABLE, $user_id);

        if (!$user || !$user['auto_reg'] || $user['send_reg_letter']) {
            return;
        }

        $subject = 'Реєстрація на ' . strtoupper(SITE_DOMAIN);

        PDO_DB::query("UPDATE " . self::TABLE . " SET send_reg_letter=1 WHERE id='$user_id' LIMIT 1");
        $email = new Email();

        return $email->send(
            array($user['email'], "{$user['name']} {$user['fathername']}"),
            $subject,
            '',
            $template,
            array(
                'username' => htmlspecialchars("{$user['name']} {$user['fathername']}"),
                'email' => $user['email'],
                'password' => htmlspecialchars($password)
            )
        );
    }

}
