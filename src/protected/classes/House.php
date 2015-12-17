<?php

class House
{   
    const TABLE = DB_TBL_HOUSES;
    
    public static function cron()
    {
        set_time_limit(0);
        self::rebuild();
    }

    public static function get_API_URL($key)
    {
        $urls = [];

        if (stristr(API_URL, 'bank.gioc')) {
            $urls['HOUSE_URL'] = '/reports/rwservlet?report=/site/dic_houses.rep&destype=Cache&Desformat=xml&cmdkey=gsity&street_id=';
        } else {
            $urls['HOUSE_URL'] = '/reports/rwservlet?report=dic_houses.rep&destype=Cache&Desformat=xml&cmdkey=gsity&street_id=';
        }

        return $urls[$key];
    }
    
    /**
     * Получение номера дома по его id и id города
     * 
     * @param  integer $house_id
     * @param  integer $city_id. OPTIONAL
     * @return string номер дома
     */
    public static function getHouseName($house_id, $city_id = self::KIEV_ID)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT house_number FROM ". self::TABLE . " WHERE city_id=? AND house_id=? LIMIT 1");
        $stm->execute(array($city_id, $house_id));
        $name = $stm->fetchColumn();
        
        if ($name === false) {
            return '';
        }
        
        return 'буд. ' . $name;
    }

    public static function get($street_id, $city_id = Street::KIEV_ID)
    {
        $pdo = PDO_DB::getPDO();
        $table = self::TABLE;
        $city_id = $pdo->quote($city_id);
        $street_id = $pdo->quote($street_id);
        
        $res = $pdo->query("SELECT * FROM $table WHERE city_id=$city_id AND street_id=$street_id ORDER BY house_number ASC");
        return $res->fetchAll();
    }

    public static function rebuild()
    {
        $streets = Street::get('');
        
        $pdo = PDO_DB::getPDO();
        $stm_del = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE city_id=? AND street_id=?");
        $stm_insert = $pdo->prepare("INSERT INTO " . self::TABLE . " SET city_id=?, street_id=?, house_id=?, house_number=?");
        
        for ($i=0; $i < count($streets); $i++) {
            $data = Http::fgets(API_URL . self::get_API_URL('HOUSE_URL') . $streets[$i]['street_id']);
            $data = iconv('CP1251', 'UTF-8', $data);
            $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
            $xml = @simplexml_load_string($data);

            if ($xml !== false) {
                $stm_del->execute(array(Street::KIEV_ID, $streets[$i]['street_id']));
                for ($j=0; $j<count($xml->ROW); $j++) {
                    $stm_insert->execute(array(Street::KIEV_ID, $streets[$i]['street_id'], $xml->ROW[$j]->HOUSE_ID, $xml->ROW[$j]->NDOM));
                }
            }
        }
        
        $stm = $pdo->prepare("DELETE FROM " . self::TABLE . " WHERE city_id=? AND street_id NOT IN (SELECT street_id FROM ". Street::TABLE ." WHERE city_id=?)");
        $stm->execute(array(Street::KIEV_ID, Street::KIEV_ID));
    }
}
