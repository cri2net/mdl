<?php

class News
{
    const TABLE = DB_TBL_NEWS;
    const IMAGES_TABLE = DB_TBL_NEWS_IMAGES;

    public static function getNewsURL($news_id, $full_path = true)
    {
        $news_id = (int)$news_id;
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->query("SELECT title FROM " . self::TABLE . " WHERE id='$news_id' LIMIT 1");
        $title = $stm->fetchColumn();
        $url = '';
        
        if (!$title) {
            return '';
        }
        if ($full_path) {
            $url = BASE_URL;
        }

        $url .= '/news/' . composeUrlKey($title) . '_' . $news_id . '/';
        return $url;
    }

    public static function search($search, $get_count = false, $order_column = 'created_at', $order_type = 'DESC', $limit = '')
    {
        $pdo = PDO_DB::getPDO();
        $search = $pdo->quote(trim($search));
        $table = self::TABLE;

        // экранируем спецсимволы оператора LIKE
        $search = str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $search);
        $search = '%' . trim($search, "'") . '%';

        $where = "WHERE is_actual=1
                   AND (
                        title LIKE '$search'
                        OR announce LIKE '$search'
                        OR `text` LIKE '$search'
                   )";

        if ($get_count) {
            $stm = $pdo->query("SELECT COUNT(*) FROM $table $where");
            return intval($stm->fetchColumn());
        }
        
        $order_by = '';
        if ($order_column) {
            $order_by = "ORDER BY $order_column ";
            $order_by .= (strtolower($order_type) == 'asc') ? 'ASC' : 'DESC';
        }
        $limit = ($limit) ? "LIMIT $limit" : '';

        $query = "SELECT id, created_at, title, announce, `text`
                   FROM $table
                   $where
                   $order_by
                   $limit
                ";
        $stm = $pdo->query($query);
        return $stm->fetchAll();
    }

    public static function getNewsTitle($news_id)
    {
        $pdo = PDO_DB::getPDO();
        $news_id = (int)$news_id;
        $stm = $pdo->query("SELECT title FROM " . self::TABLE . " WHERE id=$news_id LIMIT 1");
        $title = $stm->fetchColumn();

        if ($title === false) {
            return null;
        }
        return $title;
    }

    public static function incrementViews($news_id)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("UPDATE " . self::TABLE . " SET views = views + 1 WHERE id=? LIMIT 1");
        $stm->execute(array($news_id));
    }

    public static function fetchList($news_list)
    {
        global $MONTHS;

        if (empty($news_list)) {
            return '';
        }
        
        $output = '<div class="news-list">';
        
        for ($i=0; $i < count($news_list); $i++) {
            if (($i > 0) && ($i % 2 == 0)) {
                $output .= '</div><div class="news-list">';
            }
            
            $date = date('d ', $news_list[$i]['created_at']) . $MONTHS[date('n', $news_list[$i]['created_at'])]['ua'];
            if (date('Y') != date('Y', $news_list[$i]['created_at'])) {
                $date .= date(' Y', $news_list[$i]['created_at']);
            }

            if (mb_strlen($news_list[$i]['title'], 'UTF-8') > 50) {
                $news_list[$i]['title'] = mb_substr($news_list[$i]['title'], 0, 50, 'UTF-8') . '...';
            }

            if (!$news_list[$i]['announce']) {
                $news_list[$i]['announce'] = strip_tags($news_list[$i]['text']);
                if (mb_strlen($news_list[$i]['announce'], 'UTF-8') > 200) {
                    $news_list[$i]['announce'] = mb_substr($news_list[$i]['announce'], 0, 200, 'UTF-8') . '...';
                }
            }

            $output .= '<div class="news-item '. (($i % 2 == 0) ? 'first' : '') . '">'
                          .'<h2 class="title">'. htmlspecialchars($news_list[$i]['title']) .'</h2>'
                          .'<div class="date">'. $date .'</div>'
                          .'<div class="announce">'. ($news_list[$i]['announce']) .'</div>'
                          .'<div class="details"><a href="'. BASE_URL .'/news/'. composeURLKey($news_list[$i]['title']) . '_' . $news_list[$i]['id'] .'/">детальніше...</a></div>'
                      .'</div>';
        }
        
        $output .= '</div>';

        return $output;
    }
}
