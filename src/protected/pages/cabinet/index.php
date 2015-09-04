<?php
    if (Authorization::isLogin()) {
        require_once(ROOT . '/protected/scripts/cabinet/index_authorized.php');
    } else {
        require_once(ROOT . '/protected/scripts/cabinet/index_unauthorized.php');
    }
