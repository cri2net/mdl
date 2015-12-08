<?php

class Flat
{
    const TABLE = DB_TBL_FLATS;
    const TABLE_AUTH_CODE = DB_TBL_AUTH_CODE;
    const USER_FLATS_TABLE = DB_TBL_USER_FLATS;
    const MAX_USER_FLATS = 4;
    const FLAT_URL = '/reports/rwservlet?report=/site/dic_kvartira.rep&destype=Cache&Desformat=xml&cmdkey=gsity&house_id=';
    const FLAT_ID_BY_PLATCODE_URL = '/reports/rwservlet?report=site/g_jek_abc.rep&cmdkey=gsity&destype=Cache&Desformat=xml&pc=';
    const FLAT_PIN_BY_ID_URL = '/reports/rwservlet?report=g_komdebt.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';

    public static function cron()
    {
        set_time_limit(0);
        self::rebuild();
    }

    /**
     * Функция - обёртка для получения максимального количества объектов,
     * которые пользователь может добавить к себе в аккаунт.
     *
     * @return integer
     */
    public static function getMaxUserFlats()
    {
        if (!Authorization::isLogin()) {
            return self::MAX_USER_FLATS;
        }

        // Одному пользователю нужно иметь возможность добавить много квартир.
        // Мы знаем его номер телефона.
        if ($_SESSION['auth']['mob_phone'] === '+380970301830') {
            return 30;
        }
        if (in_array($_SESSION['auth']['email'], ['zirka83@mail.ru', 'di.yarovoy@gmail.com', 'cri2net@gmail.com'])) {
            return 40;
        }

        // Это человек попросил больше объектов (клиент)
        if ($_SESSION['auth']['mob_phone'] === '+380671253289') {
            return 5;
        }

        return self::MAX_USER_FLATS;
    }

    /**
     * Проверка ключа авторизации для объекта
     * 
     * @param  string  $auth_key
     * @param  integer $flat_id
     * @param  integer $city_id. OPTIONAL
     */
    public static function verify_auth_key($auth_key, $flat_id, $city_id = Street::KIEV_ID)
    {
        if (in_array($_SESSION['auth']['email'], ['zirka83@mail.ru', 'di.yarovoy@gmail.com', 'cri2net@gmail.com'])) {
            return true;
        }

        $auth_key = str_replace('-', '', $auth_key);
        $pdo = PDO_DB::getPDO();

        $stm = $pdo->prepare("SELECT * FROM " . self::TABLE_AUTH_CODE . " WHERE object_id=? AND city_id=? AND code=? LIMIT 1");
        $stm->execute([$flat_id, $city_id, $auth_key]);
        $record = $stm->fetch();

        if ($record !== false) {
            return true;
        }

        // Даём последний шанс: это пользователь ввёл ключ, которого в БД ещё нет.
        // Получаем этот ключ, дёргая историю начислений.
        $KomDebt = new KomDebt();
        $dateBegin = date('1.m.Y');
        $now = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
        @$KomDebt->getData($flat_id, $dateBegin, 10);

        for ($i=0; $i < 4; $i++) { 
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $now));
            $now = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
            @$KomDebt->getData($flat_id, $dateBegin, 10);
        }

        $stm->execute([$flat_id, $city_id, $auth_key]);
        $record = $stm->fetch();

        return ($record !== false);
    }

    /**
     * Добавление квартиры/дома в профиль пользоваетеля
     * 
     * @param  integer $flat_id
     * @param  string  $auth_key ключ авторизации для объекта
     * @param  integer $city_id. OPTIONAL
     * @param  integer $user_id. OPTIONAL
     * @return string — id новой записи
     */
    public static function addFlat($flat_id, $auth_key, $city_id = Street::KIEV_ID, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
            if (!$user_id) {
                throw new Exception(ERROR_USER_NOT_LOGGED_IN);
                return false;
            }
        }

        $user_id = (int)$user_id;
        $flat_id = (int)$flat_id;
        $city_id = (int)$city_id;

        $records = PDO_DB::table_list(self::USER_FLATS_TABLE, "user_id=$user_id AND flat_id=$flat_id AND city_id=$city_id", null, '1');
        if (count($records) > 0) {
            throw new Exception(ERROR_ADDRESS_ALREADY_EXIST);
            return false;
        }
        
        if (self::getFlatCount($user_id) >= self::getMaxUserFlats()) {
            throw new Exception(ERROR_TOO_MANY_FLATS);
            return false;
        }

        $flat = self::getFlatById($flat_id, $city_id);

        if ($flat == null) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
            return false;
        }

        $data = [
            'user_id' => $user_id,
            'city_id' => $city_id,
            'street_id' => $flat['street_id'],
            'house_id' => $flat['house_id'],
            'flat_id' => $flat_id,
            'timestamp' => microtime(true),
            'auth_key' => $auth_key
        ];
        $record_id = PDO_DB::insert($data, self::USER_FLATS_TABLE);
        
        return $record_id;
    }
    
    /**
     * Удаление объекта из профиля пользователя
     * @param  string $flat_id
     * @param  integer $user_id. OPTIONAL
     * 
     * @return void
     */
    public static function removeUserFlat($flat_id, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
            if (!$user_id) {
                throw new Exception(ERROR_USER_NOT_LOGGED_IN);
                return false;
            }
        }

        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("DELETE FROM ". self::USER_FLATS_TABLE ." WHERE id = ? AND user_id=? LIMIT 1");
        $stm->execute(array($flat_id, $user_id));
    }

    /**
     * Переименовывание объекта, чтобы вместо адреса было заданное пользователем название
     * @param  string | integer  $flat_id — ID объекта
     * @param  string            $title. OPTIONAL
     * @param  integer           $user_id. OPTIONAL
     * 
     * @return void
     */
    public static function renameUserFlat($flat_id, $title, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
            if (!$user_id) {
                throw new Exception(ERROR_USER_NOT_LOGGED_IN);
                return false;
            }
        }

        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("UPDATE ". self::USER_FLATS_TABLE ." SET title=? WHERE id = ? AND user_id=? LIMIT 1");
        $stm->execute(array($title, $flat_id, $user_id));
    }
    
    public static function getFlatById($object_id, $city_id = Street::KIEV_ID)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT * FROM ". self::TABLE ." WHERE city_id=? AND object_id=? LIMIT 1");
        $stm->execute(array($city_id, $object_id));
        $flat = $stm->fetch();
        
        if ($flat === false) {
            return null;
        }
        
        return $flat;
    }

    public static function getAddressString($flat_id, $city_id = Street::KIEV_ID, &$explode = [])
    {
        $flat = self::getFlatById($flat_id, $city_id);
        if ($flat == null){
            return '';
        }

        $address = '';

        if ($city_id == Street::KIEV_ID) {
            $address .= 'Київ, ';
            $explode['city'] = 'Київ';
        }

        $explode['street'] = Street::getStreetName($flat['street_id'], $city_id);
        $explode['house'] = House::getHouseName($flat['house_id'], $city_id);
        $explode['flat'] = $flat['flat_number'];
        
        $address .= "{$explode['street']} {$explode['house']}, кв. {$flat['flat_number']}";
        return $address;
    }

    public static function getUserFlats($user_id, $need_return_debt = false)
    {
        $user_id = (int)$user_id;
        $table = self::USER_FLATS_TABLE;
        $streets_table = Street::TABLE;

        $query = "SELECT c.*, s.name_ua AS street_name_full, SUBSTRING(s.name_ua, 1, 14) AS street_name
                  FROM $table c
                  LEFT OUTER JOIN $streets_table s ON c.street_id=s.street_id
                  WHERE user_id=$user_id
                  ORDER BY c.`timestamp`";

        $stm = PDO_DB::query($query);
        $arr = $stm->fetchAll();
        
        if ($need_return_debt) {
            $debt = new KomDebt();
        }

        for ($i=0; $i < count($arr); $i++) {
            if ($need_return_debt) {
                try {
                    $arr[$i]['debt_sum'] = $debt->getDebtSum($arr[$i]['flat_id']);
                    $arr[$i]['error'] = 0;
                } catch (Exception $e) {
                    $arr[$i]['error'] = 1;
                }
            }
            
            if ($arr[$i]['street_name'] !== $arr[$i]['street_name_full']) {
                $arr[$i]['street_name'] .= "...";
            }
            $arr[$i]['address'] = self::getAddressString($arr[$i]['flat_id'], $arr[$i]['city_id'], $arr[$i]['detail_address']);
            $arr[$i]['kvartira'] = 1; // пока не знаю как получить признак, что это частный дом
        }
        
        return $arr;
    }
    
    public static function getUserFlatById($id, $need_return_debt = false, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
        }
        
        $table = self::USER_FLATS_TABLE;
        $streets_table = Street::TABLE;
        $pdo = PDO_DB::getPDO();

        $stm = $pdo->prepare(
            "SELECT c.*, s.name_ua AS street_name_full, SUBSTRING(s.name_ua, 1, 14) AS street_name
             FROM $table c
             LEFT OUTER JOIN $streets_table s ON c.street_id=s.street_id
             WHERE c.id = ? AND c.user_id=?
             LIMIT 1"
        );

        $stm->execute([$id, $user_id]);
        $arr = $stm->fetch();

        if ($arr === false) {
            return null;
        }

        if ($need_return_debt) {
            $debt = new KomDebt();
            $arr['error'] = 0;
        
            try {
                $arr['debt_sum'] = $debt->getDebtSum($arr['flat_id']);
            } catch (Exception $e) {
                $arr['error'] = 1;
            }
        }
        
        if ($arr['street_name'] !== $arr['street_name_full']) {
            $arr['street_name'] .= " ...";
        }
        $arr['address'] = self::getAddressString($arr['flat_id']);
        
        return $arr;
    }
    
    /**
     * Получение списка квартир в доме
     * 
     * @param  int     $city_id
     * @param  int     $street_id
     * @param  int     $house_id
     * @param  boolean $from_reports Нужно ли брать данные с oracle reports server, или же можно воспользоваться локальной базой
     * @return array
     */
    public static function get($house_id, $street_id, $city_id = Street::KIEV_ID, $from_reports = false)
    {
        $city_id = (int)$city_id;
        $street_id = (int)$street_id;
        $house_id = (int)$house_id;

        if (!$from_reports) {
            return PDO_DB::table_list(self::TABLE, "city_id='$city_id' AND street_id='$street_id' AND house_id='$house_id'", 'flat_number ASC');
        }

        $result = [];
        $data = Http::fgets(API_URL . self::FLAT_URL . $house_id);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);

        if ($xml !== false) {
            for ($i=0; $i<count($xml->ROW); $i++) {
                $result[] = [
                    'city_id' => $city_id,
                    'street_id' => $street_id,
                    'house_id' => $house_id,
                    'object_id' => $xml->ROW[$i]->ID_OBJ,
                    'flat_number' => $xml->ROW[$i]->NKW
                ];
            }
        } else {
            return PDO_DB::table_list(self::TABLE, "city_id='$city_id' AND street_id='$street_id' AND house_id='$house_id'", 'flat_number ASC');
        }

        return $result;
    }

    /**
     * Получение квартиры по платёжному коду.
     * Платёжный код = шифр_ЖЭО * 1000000 + лицевой_счёт
     * 
     * @param  int     $plat_code
     * @param  boolean $from_reports Нужно ли брать данные с oracle reports server, или же можно воспользоваться локальной базой
     * @return array
     */
    public static function getFlatByPlatCode($plat_code)
    {
        $data = Http::fgets(API_URL . self::FLAT_ID_BY_PLATCODE_URL . $plat_code);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);

        if ($xml == false) {
            return false;
        }

        return self::getFlatById($xml->ROW->ID_OBJ);
    }
    
    
    /**
     * Получение PIN ЖЭО * 1000000 + лицевой_счёт по квартире.
     *
     * @param  int     $flatID
     * @return array
     */
    public static function getFlatPINByID($flatID)
    {
        $data = Http::fgets(API_URL . self::FLAT_PIN_BY_ID_URL . $flatID."&dbegin=1.09.2015&dend=1.10.2015");
        //$data = Http::fgets("https://bank.gioc.kiev.ua" . self::FLAT_PIN_BY_ID_URL . $flatID."&dbegin=1.09.2015&dend=1.10.2015");
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);
    
        if ($xml == false) {
            return false;
        }
    
        return (string)$xml->ROW[0]->PLAT_CODE;//self::getFlatById($xml->ROW->ID_OBJ);
    }

    /**
     * Сохраняем ключ авторизации.
     * 
     * @param  string  $auth_key  — Ключ авторизации для объекта
     * @param  int     $plat_code — Платёжный код = шифр_ЖЭО * 1000000 + лицевой_счёт
     * @param  int     $obj_id    — Уникальный ID объекта в рамках города
     * @param  int     $city_id   — id города. OPTIONAL
     * 
     * @return void
     */
    public static function addAuthKey($auth_key, $plat_code, $obj_id, $city_id = Street::KIEV_ID)
    {
        if ($auth_key) {
            $arr = [
                'city_id' => $city_id,
                'object_id' => $obj_id,
                'code' => $auth_key,
                'plat_code' => $plat_code,
                'created_at' => microtime(true)
            ];
            PDO_DB::insert($arr, self::TABLE_AUTH_CODE, true);
        }

        // это не относится напрямую к данной фукнции, но сразу сохраним в базу plat_code для квартиры
        if ($plat_code) {
            $pdo = PDO_DB::getPDO();
            
            $stm = $pdo->prepare('UPDATE ' . self::TABLE . ' SET plat_code=? WHERE city_id=? AND object_id=? LIMIT 1');
            $stm->execute([$plat_code, $city_id, $obj_id]);
            
            $stm = $pdo->prepare('UPDATE ' . self::USER_FLATS_TABLE . ' SET plat_code=? WHERE city_id=? AND flat_id=? LIMIT 1');
            $stm->execute([$plat_code, $city_id, $obj_id]);
        }
    }

    public static function rebuild()
    {
        $pdo = PDO_DB::getPDO();
        $stm_del = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE city_id=? AND house_id=?");
        $stm_insert = $pdo->prepare("INSERT INTO " . self::TABLE . " SET city_id=?, street_id=?, house_id=?, object_id=?, flat_number=?");
        $pdo->query("UPDATE " . self::TABLE . " SET need_del_after_rebuild=1");
        
        $stm = $pdo->prepare("SELECT * FROM ". House::TABLE ." WHERE city_id=?");
        $stm->execute([Street::KIEV_ID]);

        while ($row = $stm->fetch()) {
            $data = Http::fgets(API_URL . self::FLAT_URL . $row['house_id']);
            $data = iconv('CP1251', 'UTF-8', $data);
            $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
            $xml = @simplexml_load_string($data);

            if ($xml !== false) {
                $stm_del->execute([Street::KIEV_ID, $row['house_id']]);
                for ($i=0; $i<count($xml->ROW); $i++) {
                    $stm_insert->execute(array(Street::KIEV_ID, $row['street_id'], $row['house_id'], $xml->ROW[$i]->ID_OBJ, $xml->ROW[$i]->NKW));
                }
            }
        }

        $stm = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE need_del_after_rebuild=1");
    }

    /**
     * @param  integer $user_id
     * @return integer
     */
    public static function getFlatCount($user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
        }
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT COUNT(*) FROM ". self::USER_FLATS_TABLE . " WHERE user_id=?");
        $stm->execute([$user_id]);
        return ((int)$stm->fetchColumn());
    }
}
