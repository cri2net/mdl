<?php

use cri2net\php_pdo_db\PDO_DB;

class City
{
    const TABLE = DB_TBL_CITIES;

    public static function getCityById($city_id)
    {
        return PDO_DB::row_by_id(self::TABLE, $city_id);
    }
}
