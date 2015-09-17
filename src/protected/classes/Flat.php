<?php

class Flat
{
    const TABLE = DB_TBL_FLATS;
    const USER_FLATS_TABLE = DB_TBL_USER_FLATS;
    const MAX_USER_FLATS = 4;
    const FLAT_URL = '/reports/rwservlet?report=/site/dic_kvartira.rep&destype=Cache&Desformat=xml&cmdkey=gsity&house_id=';

    /**
     * Добавление квартиры/дома в профиль пользоваетеля
     * 
     * @param  integer $flat_id
     * @param  integer $city_id. OPTIONAL
     * @param  integer $user_id. OPTIONAL
     * @return string — id новой записи
     */
    public static function addFlat($flat_id, $city_id = Street::KIEV_ID, $user_id = null)
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
        
        if (self::getFlatCount($user_id) >= self::MAX_USER_FLATS) {
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
            'timestamp' => microtime(true)
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

    public static function getUserFlats($user_id)
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
        $debt = new KomDebt();
    
        for ($i=0; $i < count($arr); $i++) {
            try {
                $arr[$i]['debt_sum'] = $debt->getDebtSum($arr[$i]['flat_id']);
                $arr[$i]['error'] = 0;
            } catch (Exception $e) {
                $arr[$i]['error'] = 1;
            }
            
            if ($arr[$i]['street_name'] !== $arr[$i]['street_name_full']) {
                $arr[$i]['street_name'] .= "...";
            }
            $arr[$i]['address'] = self::getAddressString($arr[$i]['flat_id'], $arr[$i]['city_id'], $arr[$i]['detail_address']);
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

        $debt = new KomDebt();
        $arr['error'] = 0;
    
        try {
            $arr['debt_sum'] = $debt->getDebtSum($arr['flat_id']);
        } catch (Exception $e) {
            $arr['error'] = 1;
        }
        
        if ($arr['street_name'] !== $arr['street_name_full']) {
            $arr['street_name'] .= " ...";
        }
        $arr['position'] = ($i % 2 == 0) ? 1 : 2;
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
     * Заменяем то, что в локальной базе хранится данными из reports путём полного перегона этих данных
     * @return void
     */
    public static function rebuild()
    {
        $streets = Street::get('');
        
        $pdo = PDO_DB::getPDO();
        $stm_del = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE city_id=? AND house_id=?");
        $stm_insert = $pdo->prepare("INSERT INTO " . self::TABLE . " SET city_id=?, street_id=?, house_id=?, object_id=?, flat_number=?");
        
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

        // Too slow query
        // $stm = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE city_id=? AND house_id NOT IN (SELECT house_id FROM ". House::TABLE ." WHERE city_id=?)");
        // $stm->execute(array(Street::KIEV_ID, Street::KIEV_ID));
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
