<?php
    $seo_str = '';
    $_lSEO = 'KEYWORDS';

    $list = [
        'index' => 'Головна сторінка',
        'news' => 'Новини',
        'calc-devices' => 'Розрахунок за показаннями приладів',
        'calc-subsidies' => 'Розрахунок субсидій',
        'chief' => 'Керівництво',
        'foruser' => 'Користувачу',
        'contacts' => 'Контакти',
        'media' => 'Відео-матеріали',
        'news_item' => 'Конкретна новина' . $makros,
        'cabinet' => 'Особистий кабiнет',
        'registration' => 'Реєстрація',
    ];


    switch ($__route_result['controller'] . "/" . $__route_result['action']) {
        case 'page/index':
        case 'page/news':
        case 'page/calc-devices':
        case 'page/calc-subsidies':
        case 'page/chief':
        case 'page/foruser':
        case 'page/contacts':
        case 'page/media':
            $seo_str = getTextVariableValueByName($_lSEO . '_' . strtoupper($__route_result['action']));
            break;

        case 'page/cabinet':
            switch ($__route_result['values']['subpage']) {
                case 'registration':
                    $seo_str = getTextVariableValueByName($_lSEO . '_REGISTRATION');
                    break;

                default:
                    $seo_str = getTextVariableValueByName($_lSEO . '_CABINET');
            }
            break;

        case 'page/news-item':
            if ($__news_item['seo_keywords']) {
                $seo_str = $__news_item['seo_keywords'];
            } else {
                $seo_str = getTextVariableValueByName($_lSEO."_NEWS_ITEM");
                $seo_str = str_ireplace('{TITLE}', $__news_item['title'], $seo_str);
            }
            break;

        case 'static_page/index':
            $page_item = $__static_pages_array[count($__static_pages_array) - 1];
            if ($page_item['seo_keywords']) {
                $seo_str = $page_item['seo_keywords'];
            } else {
                $seo_str = $page_item['h1'];
            }
            break;
    }

    echo htmlspecialchars($seo_str, ENT_QUOTES);
