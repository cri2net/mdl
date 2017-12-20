<?php
$_lSEO = 'TITLES';

switch ($__route_result['controller'] . "/" . $__route_result['action']) {

    case 'static_page/index':
        if ($static_page['seo_title']) {
            $seo_str = @$static_page['seo_title'];
        } else {
            $seo_str = @$static_page['h1'];
        }
        break;

    case 'error/404':
        $seo_str = "ЦКС — Помилка 404";
        break;
    
    case 'page/cabinet':
        $seo_str = 'Особистий кабінет';

        switch ($__route_result['values']['subpage']) {

            case 'registration':
                $seo_str = 'Реєстрація';
                break;

            case 'verify-email':
                $seo_str = 'Підтвердження електронної пошти';
                break;

            case 'login':
                $seo_str = 'Вхід';
                break;

            case 'restore':
                $seo_str = 'Відновлення доступу';
                break;

            case 'objects':
                $seo_str = 'Об’єкти';

                if (Authorization::isLogin() && isset($__route_result['values']['id'])) {

                    $object = Flat::getUserFlatById($__route_result['values']['id']);
                    
                    if (($object !== null) && ($object['user_id'] == Authorization::getLoggedUserId())) {
                        
                        $object_title = ($object['title']) ? $object['title'] : Flat::getAddressString($object['flat_id']);
                       
                        $seo_str = trim($object_title);

                        if (isset($__route_result['values']['section'])) {
                            $sections = [
                                'bill'        => 'Рахунок до сплати',
                                'detailbill'  => 'Історія нарахувань',
                                'historybill' => 'Довідка про платежі',
                                'edit'        => 'Редагувати об’єкт',
                                'paybill'     => 'Спосіб сплати',
                                'checkout'    => 'Перенаправлення',
                                'processing'  => 'Сплата',
                            ];
                            $seo_str = $sections[$__route_result['values']['section']];
                        }
                    }
                }
                break;

            case 'page/news-item':
                if ($__news_item['seo_title']) {
                    $seo_str = $__news_item['seo_title'];
                } else {
                    $seo_str = getTextVariableValueByName($_lSEO."_NEWS_ITEM");
                    $seo_str = str_ireplace('{TITLE}', $__news_item['title'], $seo_str);
                }
                break;

            case 'instant-payments':
                $seo_str = 'Миттєві платежі';

                if (isset($__route_result['values']['section'])) {
                    $sections = [
                        'dai'            => 'Штрафи за порушення ПДР',
                        'kindergarten'   => 'Дитячий садок',
                        'cards'          => 'Перекази з карти на карту',
                        'phone'          => 'Сплата за телефон та інтернет',
                        'cks'            => 'Сплата послуг ЦКС',
                        'budget'         => 'Платежі до бюджету',
                        'requisites'     => 'Платежі за реквізитами',
                        'volia'          => 'Воля',
                        'secret-service' => 'Державна служба охорони',
                    ];
                    $seo_str = $sections[$__route_result['values']['section']];
                }
                break;

            case 'payments':
                $seo_str = 'Мої платежі';

                if (isset($__route_result['values']['section'])) {
                    $sections = [
                        'history' => 'Історія платежів',
                        'komdebt' => 'ЖКГ платежі',
                        'instant' => 'Миттєві платежі',
                        'details' => 'Деталі платежу № ',
                    ];

                    if ($__route_result['values']['section'] == 'details') {
                        $sections['details'] .= $__route_result['values']['id'];
                    }

                    $seo_str = $sections[$__route_result['values']['section']];
                }
                break;

            case 'settings':
                $seo_str = 'Налаштування профілю';
                $sections = [
                    'info' => 'Персональні дані',
                    'notifications' => 'Налаштування повідомлень',
                    'rule' => 'Управління профілем',
                ];
                $seo_str = $sections[$__route_result['values']['section']];
                break;
        }
        break;

    case 'page/payment-status':
        $seo_str = 'Статус транзакції';
        break;

    case 'page/about_cks':
    case 'page/about':
        $seo_str = 'Про нас';
        break;

    case 'page/services-list':
    case 'page/services-list_and_docs':
        $seo_str = 'Перелік послуг';
        break;

    case 'page/service-centers':
        $seo_str = 'Сервісні центри';
        break;
    case 'page/news':
        $seo_str = 'Новини';
        break;
    case 'page/feedback':
        $seo_str = 'Питання до фахівця';
        break;
    case 'page/request-services':
        $seo_str = 'Оформлення заявки';
        break;
}

if ($seo_str == '') {
    $seo_str = 'ЦКС';
} else {
    $seo_str .= ' | ЦКС';
}

echo htmlspecialchars($seo_str, ENT_QUOTES);
