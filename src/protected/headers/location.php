<?php

switch ($__route_result['controller'] . "/" . $__route_result['action']) {

    case 'page/cabinet':

        if (!isset($__route_result['values']['subpage']) && Authorization::isLogin()) {
            $new_location = BASE_URL . '/cabinet/objects/';
        } elseif (isset($__route_result['values']['subpage']) && ($__route_result['values']['subpage'] == 'settings') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/settings/info/';
        } elseif (isset($__route_result['values']['subpage']) && ($__route_result['values']['subpage'] == 'payments') && !isset($__route_result['values']['section'])) {
            $new_location = BASE_URL . '/cabinet/payments/history/';
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
