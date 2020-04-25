<?php

if (!Authorization::isLogin()) {
    return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
}
