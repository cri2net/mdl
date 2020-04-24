<?php

use cri2net\php_pdo_db\PDO_DB;

class User
{
    const TABLE = DB_TBL_USERS;

    public static function getUserIdByEmail($email)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT id FROM ". self::TABLE ." WHERE email=? AND deleted=0 LIMIT 1");
        $stm->execute([$email]);
        $column = $stm->fetchColumn();
        if ($column === false) {
            return null;
        }
        return $column;
    }

    public static function getUserIdByPhone($phone)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT id FROM ". self::TABLE ." WHERE mob_phone=? AND deleted=0 LIMIT 1");
        $stm->execute([$phone]);
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
        $stm = $pdo->prepare("SELECT * FROM ". self::TABLE ." WHERE email=? AND deleted=0 LIMIT 1");
        $stm->execute([$email]);
        $user = $stm->fetch();
        if ($user === false) {
            return null;
        }
        return $user;
    }

    /**
     * Приватный! метод для получения записи о пользователе по одному из полей.
     * Рекомендуется указывать те поля, которые уникальны
     * @param  string $column название поля в структуре таблицы с пользователями
     * @param  string $value  значение поля
     * @return assoc array | null
     */
    private static function getUserByColumn($column, $value)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT * FROM ". self::TABLE ." WHERE $column=? AND deleted=0 LIMIT 1");
        $stm->execute([$value]);
        $user = $stm->fetch();
        if ($user === false) {
            return null;
        }
        return $user;
    }

    public static function getUserByLogin($login)
    {
        return self::getUserByColumn('login', $login);
    }

    public static function getUserByPhone($phone)
    {
        return self::getUserByColumn('mob_phone', $phone);
    }

    public static function getUserById($user_id)
    {
        return self::getUserByColumn('id', $user_id);
    }

    public static function registration($data)
    {
        $password_key = generateCode();

        $data = [
            'email'        => $data['email'],
            'password'     => Authorization::generate_db_password($data['password'], $password_key),
            'password_key' => $password_key,
            'login'        => '',
            'lastname'     => $data['lastname'],
            'name'         => $data['name'],
            'fathername'   => $data['fathername'],
            'created_at'   => microtime(true),
            'mob_phone'    => $data['phone']
        ];

        $user_id = PDO_DB::insert($data, self::TABLE);
        self::sendRegLetter($user_id, $data['password'], 'registration');
        return $user_id;
    }

    public static function registerFromPayment($email, $lastname, $name, $fathername)
    {
        $password_key = generateCode();
        $password = generateCode();

        $data = [
            'email'        => $email,
            'password'     => Authorization::generate_db_password($password, $password_key),
            'password_key' => $password_key,
            'login'        => '',
            'lastname'     => $lastname,
            'name'         => $name,
            'fathername'   => $fathername . '',
            'created_at'   => microtime(true),
            'mob_phone'    => '',
            'auto_reg'     => 1,
        ];

        $user_id = PDO_DB::insert($data, self::TABLE);
        try {
            self::sendRegLetter($user_id, $password, 'auto_reg_letter');
        } catch (Exception $e) {
        }
        
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
        $verify_code = Authorization::generateUserCode($user['id'], 'verify_email');
        $verify_link = BASE_URL . '/cabinet/verify-email/' . $verify_code . '/';

        PDO_DB::query("UPDATE " . self::TABLE . " SET send_reg_letter=1 WHERE id='$user_id' LIMIT 1");
        $email = new Email();
        $email->changeMXToQuick();

        return $email->send(
            [$user['email'], "{$user['name']} {$user['fathername']}"],
            $subject,
            '',
            $template,
            [
                'username'    => htmlspecialchars("{$user['name']} {$user['fathername']}"),
                'email'       => $user['email'],
                'password'    => htmlspecialchars($password),
                'verify_link' => $verify_link,
            ]
        );
    }
}
