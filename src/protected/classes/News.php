<?php

class News
{
    const TABLE = DB_TBL_NEWS;
    const IMAGES_TABLE = DB_TBL_NEWS_IMAGES;

    public static function getNewsURL($news_id, $full_path = true)
    {
        $item = PDO_DB::row_by_id(self::TABLE, $news_id);
        $url = '';
        
        if (!$item) {
            return '';
        }
        if ($full_path) {
            $url = BASE_URL;
        }

        $url .= '/news/' . composeUrlKey($item['title']) . '_' . $item['id'] . '/';
        return $url;
    }

    public static function logView($news_id)
    {
        // тут надо залогировать просмотр новости

        self::incrementViews($news_id);
    }

    public static function incrementViews($news_id)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("UPDATE " . self::TABLE . " SET views = views + 1 WHERE id=? LIMIT 1");
        $stm->execute(array($news_id));
    }
}
