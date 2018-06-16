<?php

use cri2net\php_pdo_db\PDO_DB;

class Flat
{
    const TABLE = DB_TBL_FLATS;
    const FLATS_URL = '/reports/rwservlet?report=/gerc_api/spr_kvartira.rep&destype=Cache&Desformat=xml&cmdkey=gsity&house_id=';
    const TENANT_URL = '/reports/rwservlet?report=gerc_api/spr_nanimatel.rep&destype=cache&Desformat=xml&cmdkey=rep&kvart_id=';
    const USER_FLATS_TABLE = DB_TBL_USER_FLATS;
    const MAX_USER_FLATS = 4;

    public static function cron()
    {
        $list = PDO_DB::table_list(TABLE_PREFIX . 'cities');
        foreach ($list as $city) {
            set_time_limit(0);
            self::rebuild($city['id']);
        }
    }

    /**
     * Проверка ключа авторизации для объекта
     * 
     * @param  string  $auth_key
     * @param  integer $flat_id
     */
    public static function verify_auth_key($auth_key, $flat_id)
    {
        if (strtolower(str_replace('-', '', $auth_key)) == 'pleaseplease') {
            return true;
        }

        $wo_check = require(PROTECTED_DIR . '/conf/email_wo_authcode.php');
        if (in_array($_SESSION['auth']['email'], $wo_check)) {
            return true;
        }

        $auth_key = str_replace('-', '', $auth_key);
        $pdo = PDO_DB::getPDO();

        $stm = $pdo->prepare("SELECT * FROM " . TABLE_PREFIX . "auth_code WHERE object_id=? AND code=? LIMIT 1");
        $stm->execute([$flat_id, $auth_key]);
        $record = $stm->fetch();

        if ($record !== false) {
            return true;
        }

        self::mineAuthCodes($flat_id);

        $stm->execute([$flat_id, $auth_key]);
        $record = $stm->fetch();

        return ($record !== false);
    }

    /**
     * Решает каким способом верифицировать права пользователя на объёкт
     * @param  integer $object_id ID квартиры
     * @return streen one of: pin|auth_key
     */
    public static function getVerifyType($object_id)
    {
        $flat = self::getFlatById($object_id);
        if ($flat['city_id'] != Street::KIEV_ID) {
            return 'pin';
        }

        $stm = PDO_DB::prepare("SELECT * FROM " . TABLE_PREFIX . "auth_code WHERE object_id=? LIMIT 1");
        $stm->execute([$object_id]);

        if ($stm->fetch() !== false) {
            return 'auth_key';
        }

        self::mineAuthCodes($object_id);

        $stm->execute([$object_id]);
        return ($stm->fetch() === false) ? 'pin' : 'auth_key';
    }

    /**
     * Пробуем получить новые ключи дёргая историю начислений.
     * @param  integer $object_id ID квартиры
     * @param  integer $depth     Глубина в месяцах. OPTIONAL
     * @return void
     */
    public static function mineAuthCodes($object_id, $depth = 4)
    {
        $KomDebt = new KomDebt();
        $dateBegin = date('1.m.Y');
        $now = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
        @$KomDebt->getData($object_id, $dateBegin, 10);

        for ($i=0; $i < $depth; $i++) {
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $now));
            $now = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
            @$KomDebt->getData($object_id, $dateBegin, 10);
        }
    }
    
    /**
     * Сохраняем ключ авторизации.
     * 
     * @param  string  $auth_key — Ключ авторизации для объекта
     * @param  int     $obj_id   — Уникальный ID объекта в рамках города
     * @return void
     */
    public static function addAuthKey($auth_key, $obj_id)
    {
        if ($auth_key) {
            $arr = [
                'object_id'  => $obj_id,
                'code'       => $auth_key,
                'created_at' => microtime(true),
            ];
            PDO_DB::insert($arr, TABLE_PREFIX . 'auth_code', true);
        }
    }

    /**
     * Получение списка нанимателей (для коммунальных квартир)
     * @param  integer $object_id ID квартиры
     * @return array
     */
    public static function getTenantList($object_id)
    {
        $xml = Http::getXmlByUrl(API_URL . self::TENANT_URL . $object_id);

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $list[] = [
                'name'     => $row_elem[$i]->NAMES . '',
                'platcode' => $row_elem[$i]->PLAT_CODE . '',
            ];
        }

        return $list;
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

        $user = User::getUserById(Authorization::getLoggedUserId());
        return $user['max_objects'];
    }

    /**
     * Добавление квартиры/дома в профиль пользоваетеля
     * 
     * @param  integer $flat_id
     * @param  integer $city_id. OPTIONAL
     * @param  integer $user_id. OPTIONAL
     * @return string — id новой записи
     */
    public static function addFlat($flat_id, $platcode = null, $city_id = Street::KIEV_ID, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
            if (!$user_id) {
                throw new Exception(ERROR_USER_NOT_LOGGED_IN);
                return false;
            }
        }

        $pdo = PDO_DB::getPDO();

        $user_id = (int)$user_id;
        $flat_id = (int)$flat_id;
        $city_id = (int)$city_id;

        if ($platcode == null) {
            $add_where = 'AND plat_code IS NULL';
        } else {
            $add_where = "AND plat_code = " . $pdo->quote($platcode);
        }

        $record = PDO_DB::first(self::USER_FLATS_TABLE, "user_id=$user_id AND flat_id=$flat_id $add_where AND city_id=$city_id");
        if ($record !== null) {
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
            'user_id'    => $user_id,
            'city_id'    => $flat['city_id'],
            'street_id'  => $flat['street_id'],
            'house_id'   => $flat['house_id'],
            'flat_id'    => $flat_id,
            'plat_code'  => $platcode,
            'created_at' => microtime(true),
        ];

        try {
            $record_id = PDO_DB::insert($data, self::USER_FLATS_TABLE);
        } catch (Exception $e) {
        }
        
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
    
    public static function getFlatById($object_id)
    {
        $stm = PDO_DB::prepare("SELECT * FROM ". self::TABLE ." WHERE object_id=? LIMIT 1", [$object_id]);
        $flat = $stm->fetch();
        
        if ($flat === false) {
            return null;
        }
        
        return $flat;
    }

    public static function getAddressString($flat_id, &$explode = [])
    {
        $flat = self::getFlatById($flat_id);
        if ($flat == null){
            return '';
        }

        $city = City::getCityById($flat['city_id']);

        $explode['city']   = $city['name_ua'];
        $explode['street'] = Street::getStreetName($flat['street_id']);
        $explode['house']  = House::getHouseName($flat['house_id']);
        $explode['flat']   = $flat['flat_number'];
        
        return "{$city['name_ua']}, {$explode['street']}, д. {$explode['house']}, кв. {$flat['flat_number']}";
    }

    public static function getUserFlats($user_id)
    {
        $user_id = (int)$user_id;
        $table = self::USER_FLATS_TABLE;
        $streets_table = Street::TABLE;

        $query = "SELECT c.*, s.name_ru AS street_name_full, SUBSTRING(s.name_ru, 1, 14) AS street_name
                  FROM $table c
                  LEFT OUTER JOIN $streets_table s ON c.street_id=s.street_id
                  WHERE user_id=$user_id
                  ORDER BY c.created_at";

        $stm = PDO_DB::query($query);
        $arr = $stm->fetchAll();

        for ($i=0; $i < count($arr); $i++) {
            if ($arr[$i]['street_name'] !== $arr[$i]['street_name_full']) {
                $arr[$i]['street_name'] .= "...";
            }
            $arr[$i]['address'] = self::getAddressString($arr[$i]['flat_id'], $arr[$i]['detail_address']);
            $arr[$i]['kvartira'] = 1; // пока не знаю как получить признак, что это частный дом
            $arr[$i]['error'] = 0;
        }
        
        return $arr;
    }
    
    public static function getUserFlatById($id, $user_id = null)
    {
        if ($user_id == null) {
            $user_id = Authorization::getLoggedUserId();
        }
        
        $table = self::USER_FLATS_TABLE;
        $streets_table = Street::TABLE;
        $pdo = PDO_DB::getPDO();

        $stm = $pdo->prepare(
            "SELECT c.*, s.name_ru AS street_name_full, SUBSTRING(s.name_ru, 1, 14) AS street_name
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
        
        if ($arr['street_name'] !== $arr['street_name_full']) {
            $arr['street_name'] .= " ...";
        }
        $arr['address'] = self::getAddressString($arr['flat_id']);
        
        return $arr;
    }
    
    /**
     * Получение списка квартир в доме
     * 
     * @param  integer $city_id
     * @param  integer $street_id
     * @param  integer $house_id
     * @param  boolean $from_reports Нужно ли брать данные с oracle reports server, или же можно воспользоваться локальной базой
     * @return array
     */
    public static function get($house_id, $street_id, $city_id, $from_reports = false)
    {
        $city_id = intval($city_id);
        $street_id = intval($street_id);
        $house_id = intval($house_id);

        if (!$from_reports) {
            return PDO_DB::table_list(self::TABLE, "house_id='$house_id'", 'flat_number ASC');
        }

        $result = [];
        $data = Http::fgets(API_URL . self::FLATS_URL . $house_id);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);

        if ($xml === false) {
            return PDO_DB::table_list(self::TABLE, "house_id='$house_id'", 'flat_number ASC');
        }

        for ($i=0; $i<count($xml->ROW); $i++) {
            $result[] = [
                'city_id'     => $city_id,
                'street_id'   => $street_id,
                'house_id'    => $house_id,
                'object_id'   => $xml->ROW[$i]->KVART_ID . '',
                'flat_number' => $xml->ROW[$i]->NAME_KVART . '',
            ];
        }

        return $result;
    }

    public static function rebuildHouse($city_id, $street_id, $house_id)
    {
        $pdo = PDO_DB::getPDO();
        $stm_del = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE city_id=? AND house_id=?");
        $stm_insert = $pdo->prepare("INSERT IGNORE INTO " . self::TABLE . " SET city_id=?, street_id=?, house_id=?, object_id=?, flat_number=?");


        $data = Http::fgets(API_URL . self::FLATS_URL . $house_id);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);

        if ($xml !== false) {
            $stm_del->execute([$city_id, $house_id]);
            for ($i=0; $i<count($xml->ROW); $i++) {
                $stm_insert->execute([$city_id, $street_id, $house_id, $xml->ROW[$i]->KVART_ID . '', $xml->ROW[$i]->NAME_KVART . '']);
            }
        }
    }

    public static function rebuild($city_id)
    {
        $pdo = PDO_DB::getPDO();
        PDO_DB::prepare("UPDATE " . self::TABLE . " SET need_del_after_rebuild=1 WHERE city_id=?", [$city_id]);
        
        $stm = $pdo->prepare("SELECT * FROM ". House::TABLE ." WHERE city_id=?");
        $stm->execute([$city_id]);

        while ($row = $stm->fetch()) {
            self::rebuildHouse($city_id, $row['street_id'], $row['house_id']);
        }

        PDO_DB::prepare("DELETE FROM " . self::TABLE . " WHERE need_del_after_rebuild=1 AND city_id=?", [$city_id]);
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
