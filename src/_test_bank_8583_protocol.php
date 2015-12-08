<?php
require_once(__DIR__ . '/protected/config.php');


if (!in_array(USER_REAL_IP, ['46.151.192.106', '195.138.83.178', '127.0.0.1'])) {
    exit('Access denied');
}

$jak = new JAK8583();
$Khreshchatyk = new Khreshchatyk();
