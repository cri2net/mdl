<?php
    if (Authorization::isLogin()) {
        if (isset($__route_result['values']['id'])) {
            require_once(ROOT . '/protected/scripts/cabinet/object-item.php');
        } else {
            require_once(ROOT . '/protected/scripts/cabinet/objects.php');
        }
    } else {
        require_once(ROOT . '/protected/scripts/cabinet/object-no-auth.php');
    }
