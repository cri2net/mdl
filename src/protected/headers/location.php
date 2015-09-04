<?php
    
    // перекидываем заходы по ссылкам старого сайта на новые URL:
    
    if (preg_match('/\/main\/document\/([1-9][0-9]{0,15})\//i', $_SERVER['REQUEST_URI'], $matches)) {
        $list = PDO_DB::table_list(News::TABLE, 'old_site_id=' . ((int)$matches[1]), null, '1');
        
        if (count($list) == 1) {
            $new_location = News::getNewsURL($list[0]['id']);
        }
    }
    
    switch($__route_result['controller'] . "/" . $__route_result['action']) {

        case 'page/news-item':
            // проверка, существует ли новость
            // в целях оптимизации потом будем использовать эту переменную, так что выделяем её подчёркиваниями
            $__news_item = PDO_DB::row_by_id(News::TABLE, $__route_result['values']['news_id']);
            if (!$__news_item) {
                // Новость не существует, перекидываем на список новостей
                $new_location = BASE_URL . '/news/';
            } elseif (strcmp(composeUrlKey($__news_item['title']), $__route_result['values']['title']) !== 0) {
                // проверяем ЧПУ новости
                $new_location = News::getNewsURL($__news_item['id']);
            }
            break;
    }

    // switch (trim($_SERVER['REQUEST_URI'], '/')) {
    //     case 'main':
    //         $new_location = BASE_URL . '/about/';
    //         break;

    //     case 'main/history':
    //         $new_location = BASE_URL . '/about/history/';
    //         break;

    //     case 'main/about/workschedule':
    //         $new_location = BASE_URL . '/contacts/#page-map-clock';
    //         break;
    // }

    if (isset($new_location) && $new_location) {
        Http::redirect($new_location);
    }
