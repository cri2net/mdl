<?php

use cri2net\php_pdo_db\PDO_DB;

class Street
{   
    const TABLE = DB_TBL_STREETS;
    const ODESSA_ID = 1;
    const KIEV_ID = 447;
    const STREET_URL = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=/gerc_api/spr_street.rep&destype=Cache&Desformat=xml&cmdkey=gsity&sity_id=';
    
    public static function cron()
    {
        $list = PDO_DB::table_list(TABLE_PREFIX . 'cities');
        foreach ($list as $city) {
            set_time_limit(0);
            self::rebuild($city['id']);
        }
    }

    public static function getStreetName($street_id)
    {
        $street = PDO_DB::row_by_id(self::TABLE, $street_id, 'street_id');
        if ($street === null) {
            return '';
        }
        
        return $street['name_ru'];
    }

    public static function get($q, $city_id, $limit = 0)
    {
        $pdo = PDO_DB::getPDO();
        $limit = (int)$limit;
        $limit = ($limit > 0) ? "LIMIT $limit" : '';
        $table = self::TABLE;
        $city_id = $pdo->quote($city_id);
        $q = $pdo->quote(trim($q) . '%');
        
        $res = $pdo->query("SELECT * FROM $table WHERE city_id=$city_id AND name_ru LIKE $q ORDER BY name_ru ASC $limit");
        return $res->fetchAll();
    }

    public static function rebuild($city_id)
    {
        $url = self::STREET_URL . $city_id;
        $data = Http::fgets($url);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);
        
        if ($xml === false) {
            return false;
        }

        PDO_DB::prepare("DELETE FROM ". self::TABLE ." WHERE city_id=?", [$city_id]);

        for ($i=0; $i<count($xml->ROW); $i++) {
            $street = [
                'city_id'   => $city_id,
                'street_id' => $xml->ROW[$i]->STREET_ID,
                'name_ru'   => $xml->ROW[$i]->NAME_STREET
            ];
            PDO_DB::insert($street, self::TABLE, true);
        }
    }
}
