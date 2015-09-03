<?php

class StaticPage
{   
    const TABLE = DB_TBL_PAGES;
    const TABLE_VIEWS = DB_TBL_PAGE_VIEWS;
    const TABLE_LINKS = DB_TBL_PAGES_LINKS;

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

    /**
     * Сохраняем в БД посещение страницы.
     * Метод может логировать посещения не только статических страниц, но и любых других.
     * 
     * @param  string | integer  $page_id   ID посещённой страницы. Может быть строкой, например, 'contacts'
     * @param  string            $page_type Тип посещйнной страницы. OPTIONAL
     * @param  integer           $user_id   ID пользователя, который просматривает страницы. Значение 0 для "гостей". OPTIONAL
     * @return void
     */
    public static function logView($page_id, $page_type = 'static_page', $user_id = null)
    {
        if (preg_match('/robot|spider|crawler|curl|^$/i', HTTP_USER_AGENT)) {
            // не логируем действия ботов
            return;
        }

        if ($user_id === null) {
            $user_id = Authorization::getLoggedUserId();
            if (!$user_id) {
                $user_id = 0;
            }
        }

        $arr = array(
            'timestamp' => microtime(true),
            'page_type' => $page_type,
            'page_id' => $page_id,
            'user_id' => $user_id,
            'ip' => USER_REAL_IP,
            'user_agent_string' => HTTP_USER_AGENT,
        );

        PDO_DB::insert($arr, self::TABLE_VIEWS);
    }

    public static function incrementViews($id)
    {
        $id = (int)$id;
        $table = self::TABLE;
        PDO_DB::query("UPDATE $table SET views = views + 1 WHERE id='$id' LIMIT 1");
        self::logView($id);
    }

    public static function getChildren($id)
    {
        $id = (int)$id;
        return PDO_DB::table_list(self::TABLE, "is_active=1 AND idp=$id", 'pos ASC');
    }

    public static function getSeeAlso($id)
    {
        $id = (int)$id;
        return PDO_DB::table_list(self::TABLE_LINKS, "is_active=1 AND idp=$id AND type='see_also'", 'pos ASC');
    }
}
