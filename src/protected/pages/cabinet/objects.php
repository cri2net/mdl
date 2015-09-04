<?php
    if (Authorization::isLogin()) {
        require_once(ROOT . '/protected/scripts/cabinet/objects.php');
    } else {
        require_once(ROOT . '/protected/scripts/cabinet/object-no-auth.php');
    }
