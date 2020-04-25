<?php

switch ($__route_result['controller'] . "/" . $__route_result['action']) {

    case 'page/index':
        $new_location = BASE_URL . '/cabinet/';
        break;

    case 'page/cabinet':

        $subpage = $__route_result['values']['subpage'] ?? '';

        if (in_array($subpage, ['', 'login', 'registration']) && Authorization::isLogin()) {
            $new_location = BASE_URL . '/cabinet/objects/';
            break;
        }

        if (($subpage == 'settings') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/settings/info/';
        } elseif (($subpage == 'payments') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/payments/history/';
        } elseif (
            isset($subpage)
            && ($subpage == 'objects')
            && isset($__route_result['values']['id'])
            && !isset($__route_result['values']['section'])
        ) {
            $new_location = BASE_URL . "/cabinet/objects/{$__route_result['values']['id']}/bill/";
        } elseif (
            isset($subpage)
            && ($subpage == 'payments')
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
