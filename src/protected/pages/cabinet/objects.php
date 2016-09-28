<?php

if (!Authorization::isLogin()) {
    define('SHOW_NEED_AUTH_MESSAGE', true);
    return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
}

if (isset($__route_result['values']['id'])) {
    require_once(PROTECTED_DIR . '/scripts/cabinet/object-item.php');
} else {
    require_once(PROTECTED_DIR . '/scripts/cabinet/objects.php');
}
