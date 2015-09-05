<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    } else {
        // require_once(ROOT . '/protected/scripts/cabinet/object-no-auth.php');
    }
