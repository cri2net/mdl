<?php

use cri2net\php_pdo_db\PDO_DB;

class House
{   
    const HOUSES_URL = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=/gerc_api/spr_houses.rep&destype=Cache&Desformat=xml&cmdkey=gsity&street_id=';
    const TABLE = DB_TBL_HOUSES;
    
    public static function cron()
    {
        $list = PDO_DB::table_list(TABLE_PREFIX . 'cities');
        foreach ($list as $city) {
            set_time_limit(0);
            self::rebuild($city['id']);
        }
    }

    /**
     * Получение номера дома по его id и id города
     * 
     * @param  integer $house_id
     * @return string номер дома
     */
    public static function getHouseName($house_id)
    {
        $stm = PDO_DB::prepare("SELECT house_number FROM ". self::TABLE . " WHERE house_id=? LIMIT 1", [$house_id]);
        $name = $stm->fetchColumn();
        
        if ($name === false) {
            return '';
        }
        
        return 'буд. ' . $name;
    }

    public static function get($street_id)
    {
        $stm = PDO_DB::prepare("SELECT * FROM " . self::TABLE . " WHERE street_id=? ORDER BY house_number ASC", [$street_id]);
        return $stm->fetchAll();
    }

    public static function rebuildStreet($city_id, $street_id)
    {
        $data = Http::fgets(self::HOUSES_URL . $street_id);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);

        $pdo = PDO_DB::getPDO();
        $stm_insert = $pdo->prepare("INSERT IGNORE INTO " . self::TABLE . " SET city_id=?, street_id=?, house_id=?, house_number=?");

        if ($xml !== false) {
            for ($j=0; $j<count($xml->ROW); $j++) {
                $stm_insert->execute([$city_id, $street_id, $xml->ROW[$j]->HOUSE_ID, $xml->ROW[$j]->NDOM]);
            }
        }

        if ($xml !== false) {
            for ($j=0; $j<count($xml->ROW); $j++) {
                $stm_insert->execute([$city_id, $streets[$i]['street_id'], $xml->ROW[$j]->HOUSE_ID, $xml->ROW[$j]->NDOM]);
            }
        }
    }

    public static function rebuild($city_id)
    {
        $streets = Street::get('', $city_id);
        PDO_DB::prepare("DELETE FROM " . self::TABLE . " WHERE city_id=?", [$city_id]);
        
        for ($i=0; $i < count($streets); $i++) {
            self::rebuildStreet($city_id, $streets[$i]['street_id']);
        }
    }
}
