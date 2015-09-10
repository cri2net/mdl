<?php
    
    // перекидываем заходы по ссылкам старого сайта на новые URL:
    
    if (preg_match('/^\/main\/document\/([1-9][0-9]{0,15})\//i', $_SERVER['REQUEST_URI'], $matches)) {

        $static_page_arr = array(
         
            // это "Перелік розробок"
            '12' => 155, '23' => 156, '32' => 157, '33' => 158, '34' => 159, '35' => 160, '36' => 161, '37' => 162, '28' => 163, '27' => 164, '25' => 165, '30' => 166, '26' => 167, '29' => 168, '216' => 169, '19' => 170, '20' => 171, '21' => 172, '22' => 173, '10' => 174, '17' => 175, '18' => 176, '15' => 177, '14' => 178, '215' => 179, '214' => 180,

            // Правові документи / Інформація для споживача щодо неякісно наданих послуг
            '252' => 181, '159' => 182, '128' => 183, '157' => 184, '156' => 185, '127' => 186, '126' => 187, '125' => 188, '124' => 189, '123' => 190, '122' => 191,
            
            '60'  => '/about/history/',
            '40'  => '/law/priveleges/',
            '134' => '/law/salary/',
            '119' => '/foruser/terminals/',
            '146' => '/foruser/terminals/',
            '353' => '/law/tariff/',

            // Про ОСББ
            '250' => '/law/osbb/',
            '182' => '/law/osbb/',
            '183' => '/law/osbb/',
            '184' => '/law/osbb/',
            '185' => '/law/osbb/',
            '186' => '/law/osbb/',
            '187' => '/law/osbb/',
            '189' => '/law/osbb/',

            // Корисні посилання
            '50'  => '/foruser/helplinks/links/',
            '48'  => '/foruser/helplinks/links/',
            '65'  => '/foruser/helplinks/links/',
            '49'  => '/foruser/helplinks/links/',
            '51'  => '/foruser/helplinks/links/',
            '52'  => '/foruser/helplinks/links/',
            '53'  => '/foruser/helplinks/links/',
            '54'  => '/foruser/helplinks/links/',

            '42'  => '/about/media/textmedia/',
            '64'  => '/about/service/',
            '358' => '/about/managment/perspectives/',
            '312' => '/about/procurements/',
            '239' => '/about/procurements/',

            '258' => '/law/compensation/',
            '259' => '/law/compensation/',
            '160' => '/law/badfacilities/',
            '135' => '/law/badfacilities/',
            '364' => '/law/etc/',
            '354' => '/law/etc/',
            '263' => '/law/etc/',
            '47'  => '/law/etc/',
            '43'  => '/law/etc/',
            '77'  => '/law/kmda/',
            '74'  => '/law/kmda/',
            '73'  => '/law/kmda/',
            '72'  => '/law/kmda/',
            '71'  => '/law/kmda/',
            '116' => '/law/kmda/',
            '130' => '/law/kmu/',
            '131' => '/law/kmu/',
        );

        if (isset($static_page_arr[$matches[1]])) {
            if (is_int($static_page_arr[$matches[1]])) {
                $new_location = BASE_URL . StaticPage::getPath($static_page_arr[$matches[1]]);
            } else {
                $new_location = BASE_URL . $static_page_arr[$matches[1]];
            }
        } else {
            // ищем по новостям
            $list = PDO_DB::table_list(News::TABLE, 'old_site_id=' . ((int)$matches[1]), null, '1');
            
            if (count($list) == 1) {
                $new_location = News::getNewsURL($list[0]['id']);
            }
        }
    } else {
    
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
            
            case 'page/cabinet':
                if (!isset($__route_result['values']['subpage']) && Authorization::isLogin()) {
                    $new_location = BASE_URL . '/cabinet/objects/';
                } elseif (($__route_result['values']['subpage'] == 'settings') && !isset($__route_result['values']['section'])) {
                    $new_location = BASE_URL . '/cabinet/settings/info/';
                } elseif (
                    ($__route_result['values']['subpage'] == 'objects')
                    && isset($__route_result['values']['id'])
                    && !isset($__route_result['values']['section'])
                ) {
                    $new_location = BASE_URL . "/cabinet/objects/{$__route_result['values']['id']}/bill/";
                }
                break;
        }

        $uri_assoc_arr = array(
            'main'                      => '/about/',
            'main/history'              => '/about/history/',
            'main/about/workschedule'   => '/contacts/#page-map-clock',
            'main/contact'              => '/contacts/',
            'main/contact/map'          => '/contacts/#page-map-marker',
            'main/about/chief'          => '/about/chief/',
            'main/about/program'        => '/about/program/',
            'main/about/service'        => '/about/service/',
            'main/managment'            => '/about/managment/',
            'main/procurements'         => '/about/procurements/',
            'main/history/achievements' => '/about/strides/',
            'main/media'                => '/about/media/video/',
            'main/banks'                => '/foruser/banks/',
            'main/terminals'            => '/foruser/terminals/',
            'main/compensation'         => '/law/compensation/',
            'main/law/tariff'           => '/law/compensation/',
            'calcss'                    => '/calc-subsidies/',
            'calc'                      => '/calc-devices/',
        );

        if (isset($uri_assoc_arr[trim($_SERVER['REQUEST_URI'], '/')])) {
            $new_location = BASE_URL . $uri_assoc_arr[trim($_SERVER['REQUEST_URI'], '/')];
        } elseif (preg_match('/^\/main\/law\//i', $_SERVER['REQUEST_URI'], $matches)) {
            $new_location = BASE_URL . str_replace('/main/law/', '/law/', $_SERVER['REQUEST_URI']);
        }
    }

    
    if (isset($new_location) && $new_location) {
        Http::redirect($new_location);
    }
