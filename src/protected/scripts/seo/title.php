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
        $seo_str = "КМДА — Помилка 404";
        break;
    
    case 'page/cabinet':
        $seo_str = 'Особистий кабінет';

        switch ($__route_result['values']['subpage']) {

            case 'verify-email':
                $seo_str = 'Підтвердження електронної пошти';
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

            case 'payments':
                $seo_str = 'Мої платежі';

                if (isset($__route_result['values']['section'])) {
                    $sections = [
                        'history' => 'Історія платежів',
                        'komdebt' => 'ЖКГ платежі',
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

    default:
        $seo_str = '';
}

if ($seo_str == '') {
    $seo_str = 'КМДА';
} else {
    $seo_str .= ' | КМДА';
}

echo htmlspecialchars($seo_str, ENT_QUOTES);
