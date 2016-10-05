<?php

use cri2net\php_pdo_db\PDO_DB;

switch ($__route_result['controller'] . "/" . $__route_result['action']) {
    
    case 'error/404':
        $uri_assoc_arr = [
            // сахар для URI (типа как синтаксический)
            'gai'          => '/cabinet/instant-payments/dai/',
            'dai'          => '/cabinet/instant-payments/dai/',
            'kindergarten' => '/cabinet/instant-payments/kindergarten/',
            'phone'        => '/cabinet/instant-payments/phone/',
            'cards'        => '/cabinet/instant-payments/cards/',
        ];

        if (isset($uri_assoc_arr[trim($_SERVER['REQUEST_URI'], '/')])) {
            $new_location = BASE_URL . $uri_assoc_arr[trim($_SERVER['REQUEST_URI'], '/')];
        }

    break;

    case 'page/cabinet':
        if (!isset($__route_result['values']['subpage']) && Authorization::isLogin()) {
            $new_location = BASE_URL . '/cabinet/objects/';
        } elseif (isset($__route_result['values']['subpage']) && ($__route_result['values']['subpage'] == 'settings') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/settings/info/';
        } elseif (isset($__route_result['values']['subpage']) && ($__route_result['values']['subpage'] == 'payments') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/payments/history/';
        } elseif (isset($__route_result['values']['subpage']) && ($__route_result['values']['subpage'] == 'instant-payments') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/instant-payments/cks/';
        } elseif (
            isset($__route_result['values']['subpage'])
            && ($__route_result['values']['subpage'] == 'objects')
            && isset($__route_result['values']['id'])
            && !isset($__route_result['values']['section'])
        ) {
            $new_location = BASE_URL . "/cabinet/objects/{$__route_result['values']['id']}/bill/";
        } elseif (
            isset($__route_result['values']['subpage'])
            && ($__route_result['values']['subpage'] == 'payments')
            && ($__route_result['values']['section'] == 'details')
            && !isset($__route_result['values']['id'])
        ) {
            $new_location = BASE_URL . "/cabinet/payments/";
        }
        break;
}

if (isset($new_location) && $new_location) {
    Http::redirect($new_location);
}
