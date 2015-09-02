<?php
    if (Authorization::isLogin()) {
        require_once(__DIR__ . '/index_authorized.php');
    } else {
        require_once(__DIR__ . '/index_unauthorized.php');
    }
