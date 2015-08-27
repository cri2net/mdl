<?php
    
    // перекидываем заходы по ссылкам старого сайта на новые URL:
    
    if (preg_match('/\/main\/document\/([1-9][0-9]{0,15})\//i', $_SERVER['REQUEST_URI'], $matches)) {
        $list = PDO_DB::table_list(News::TABLE, 'old_site_id=' . ((int)$matches[1]), null, '1');
        
        if (count($list) == 1) {
            $new_location = News::getNewsURL($list[0]['id']);
        }
    }

    if (isset($new_location) && $new_location) {
        Http::redirect($new_location);
    }
