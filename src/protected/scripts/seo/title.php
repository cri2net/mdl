<?php
$_lSEO = 'TITLES';

switch ($__route_result['controller'] . "/" . $__route_result['action']) {

    case 'error/404':
        $seo_str = "МДЛ — Помилка 404";
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
                                'paybill'     => 'Сплата',
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
                    'info'           => 'Персональні дані',
                    'delete_profile' => 'Видалити профіль',
                ];
                $seo_str = $sections[$__route_result['values']['section']];
                break;
        }
        break;

    default:
        $seo_str = '';
}

if ($seo_str == '') {
    $seo_str = 'МДЛ';
} else {
    $seo_str .= ' | МДЛ';
}

echo htmlspecialchars($seo_str, ENT_QUOTES);
