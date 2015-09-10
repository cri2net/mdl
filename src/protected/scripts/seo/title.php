<?php
    $seo_title = "КП ГіОЦ";

    switch($__route_result['controller'] . "/" . $__route_result['action']) {
        case 'page/index':
            $seo_title = "КП ГіОЦ — Головна";
            break;

        case 'page/about':
            $seo_title = "КП ГіОЦ — про нас";
            break;
        case 'page/media':
            $seo_title = "КП ГіОЦ — Медiа";
            break;

        case 'page/foruser':
            $seo_title = "КП ГіОЦ — Споживачу";
            break;

        case 'page/chief':
            $seo_title = "КП ГіОЦ — Керівництво";
            break;

        case 'page/calc-devices':
            $seo_title = "КП ГіОЦ — Розрахунок за показаннями квартирних приладів обліку";
            break;
        case 'page/calc-subsidies':
            $seo_title = "КП ГіОЦ — Орієнтовний онлайн розрахунок субсидій";
            break;

        case 'page/cabinet':
            $seo_title = "КП ГіОЦ — Особистий кабiнет";
            break;

        case 'page/contacts':
            $seo_title = "КП ГіОЦ — Контакти";
            break;

        case 'page/news':
            $seo_title = "КП ГіОЦ — Новини";
            break;

        case 'page/news-item':
            if ($__news_item['seo_title']) {
                $seo_title = $__news_item['seo_title'];
            } else {
                $seo_title = "КП ГіОЦ — " . $__news_item['title'];
            }
            break;

        case 'static_page/index':
            $seo_title = "КП ГіОЦ — " . $__static_pages_array[count($__static_pages_array) - 1]['h1'];
            break;

        case 'error/404':
            $seo_title = "КП ГіОЦ — Помилка 404";
            break;
    }

    echo htmlspecialchars($seo_title, ENT_QUOTES);
