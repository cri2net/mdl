<?php

class User
{
    const TABLE = DB_TBL_USERS;
    const SUBSCRIBE_TABLE = DB_TBL_SUBSCRIBES;

    public static function getUserIdByEmail($email)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT id FROM ". DB_TBL_USERS ." WHERE email=? AND deleted=0 LIMIT 1");
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
        $stm = $pdo->prepare("SELECT id FROM ". DB_TBL_USERS ." WHERE mob_phone=? AND deleted=0 LIMIT 1");
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
        $stm = $pdo->prepare("SELECT * FROM ". DB_TBL_USERS ." WHERE email=? AND deleted=0 LIMIT 1");
        $stm->execute([$email]);
        $user = $stm->fetch();
        if ($user === false) {
            return null;
        }
        return $user;
    }

    /**
     * Получение карты пользователя
     * @param  integer $card_id id карты в БД сайта
     * @param  integer $user_id id пользователя, у которого ищем карту. OPTIONAL
     * @return assoc array | null данные карты
     */
    public static function getUserCard($card_id, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
        }
        $pdo = PDO_DB::getPDO();

        $stm = $pdo->prepare("SELECT * FROM ". TABLE_PREFIX ."user_cards WHERE id=? AND user_id=? LIMIT 1");
        $stm->execute([$card_id, $user_id]);
        $card = $stm->fetch();
        
        if ($card === false) {
            return null;
        }

        $card['additional'] = (array)(@json_decode($card['additional']));

        return $card;
    }

    public static function updateUserCardData($card_id)
    {
        $card = self::getUserCard($card_id);
        if (!$card) {
            return;
        }

        if ($card['type'] == 'khreshchatyk') {
            $khreshchatyk = new Khreshchatyk;
            $actual_card_data = $khreshchatyk->getCardData($card['pan']);
            $additional = $card['additional'];
            
            if ($actual_card_data) {
                $additional['acc_bank'] = $actual_card_data['acc_bank'];
                $additional['card_state_id'] = $actual_card_data['card_state_id'];

                $arr = [
                    'updated_at' => microtime(true),
                    'additional' => json_encode($additional)
                ];
                if ($actual_card_data['card_state_id'] != 5) {
                    $arr['is_work'] = 0;
                }

                PDO_DB::update($arr, TABLE_PREFIX . 'user_cards', $card['id']);
            }
        }
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
        $stm = $pdo->prepare("SELECT * FROM ". DB_TBL_USERS ." WHERE $column=? AND deleted=0 LIMIT 1");
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
        self::sendRegLetter($user_id, $password, 'registration');
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
            'fathername'   => $fathername,
            'created_at'   => microtime(true),
            'mob_phone'    => '',
            'auto_reg'     => 1,
        ];

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
                'verify_link' => $verify_link
            ]
        );
    }

    /**
     * Осуществляем подписку по email. Сознательно не делаем никакой связи с таблицей пользователей.
     * @param  string $email
     * @return integer        id записи.
     */
    public static function subscribeByEmail($email)
    {
        $pdo = PDO_DB::getPDO();
        $_email = $pdo->quote($email);
        $subscriber = PDO_DB::first(self::SUBSCRIBE_TABLE, "email=$_email");
        $time = microtime(true);

        if (empty($subscriber)) {
            $insert = [
                'email' => $email,
                'created_at' => $time,
                'updated_at' => $time
            ];
            return PDO_DB::insert($insert, self::SUBSCRIBE_TABLE);
        }

        PDO_DB::update(['updated_at' => $time, 'subscribe' => 1], self::SUBSCRIBE_TABLE, $subscriber['id']);
        return $subscriber['id'];
    }

}
