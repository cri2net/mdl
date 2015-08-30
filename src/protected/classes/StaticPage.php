<?php

class StaticPage
{   
    const TABLE = DB_TBL_PAGES;

    public static function getByURI($uri = null, &$results = null)
    {
        if ($uri == null) {
            $uri = $_SERVER['REQUEST_URI'];
        }
        $uri = (strpos($uri, '?') !== false) ? substr($uri, 0, strrpos($uri, '?')) : $uri;

        $levels = explode('/', $uri);
        $results = array();
        $idp = 0;

        for ($i=0; $i < count($levels); $i++) {
            if (strlen($levels[$i]) > 0) {
                $page = self::getByKey($levels[$i], $idp);
                if (!$page) {
                    return null;
                }
                $idp = $page['id'];
                $results[] = $page;
            }
        }

        if (count($results) > 0) {
            return $results[count($results) - 1];
        }

        return null;
    }

    public static function getByKey($key, $idp)
    {
        $pdo = PDO_DB::getPDO();

        // поле is_active пока игнорируем
        $stm = $pdo->prepare("SELECT * FROM " . self::TABLE . " WHERE idp=? AND `key`=? LIMIT 1");
        $stm->execute(array($idp, $key));
        $record = $stm->fetch();
        
        if ($record === false) {
            return null;
        }
        
        return $record;
    }

    public static function getById($id)
    {
        return PDO_DB::row_by_id(self::TABLE, $id);
    }

    public static function getPath($id)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT id, idp, `key` FROM " . self::TABLE . " WHERE id=? LIMIT 1");
        $stm->execute(array($id));
        $item = $stm->fetch();

        if (!$item) {
            return '/';
        }

        $keys = array($item['key']);
        $idp = $item['idp'];

        while ($idp != 0) {
            $stm->execute(array($idp));
            $tmp = $stm->fetch();
            $idp = $tmp['idp'];
            $keys[] = $tmp['key'];
        }

        $path = '/';
        for ($i=count($keys) -1; $i >= 0; $i--) {
            $path .= $keys[$i] . '/';
        }

        return $path;
    }

    public static function getChildren($id)
    {
        $id = (int)$id;
        return PDO_DB::table_list(self::TABLE, "is_active=1 AND idp=$id", 'pos ASC');
    }
}
