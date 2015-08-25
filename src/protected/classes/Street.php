<?php

class Street
{   
    const TABLE = DB_TBL_STREETS;
    const KIEV_ID = 100;
    const STREET_URL = '/reports/rwservlet?report=/home/oracle/reports/site/dic_streets.rep&destype=Cache&Desformat=xml&cmdkey=gsity';
    
    public static function getStreetName($street_id, $city_id = self::KIEV_ID)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT name_ua FROM ". self::TABLE . " WHERE city_id=? AND street_id=? LIMIT 1");
        $stm->execute(array($city_id, $street_id));
        $name = $stm->fetchColumn();
        
        if ($name === false) {
            return '';
        }
        
        return $name;
    }

    public static function get($q, $city_id = self::KIEV_ID, $limit=0)
    {
        $pdo = PDO_DB::getPDO();
        $limit = (int)$limit;
        $limit = ($limit > 0) ? "LIMIT $limit" : '';
        $table = self::TABLE;
        $city_id = $pdo->quote($city_id);
        $q = $pdo->quote('%' . trim($q) . '%');
        
        $res = $pdo->query("SELECT * FROM $table WHERE city_id=$city_id AND name_ua LIKE $q ORDER BY name_ua ASC $limit");
        return $res->fetchAll();
    }

    public static function rebuild()
    {
        $data = Http::httpGet(API_URL . self::STREET_URL);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);
        
        if ($xml === false) {
            return false;
        }

        PDO_DB::query("DELETE FROM ". self::TABLE ." WHERE city_id=" . self::KIEV_ID);

        for ($i=0; $i<count($xml->ROW); $i++) {
            $street = array(
                'city_id' => self::KIEV_ID,
                'street_id' => $xml->ROW[$i]->STREET_ID,
                'name_ua' => $xml->ROW[$i]->NAME_STREET
            );
            PDO_DB::insert($street, self::TABLE);
        }
    }
}
