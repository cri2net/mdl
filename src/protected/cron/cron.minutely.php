<?php
if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
}

require_once(__DIR__ . '/../config.php');
ShoppingCart::cron();

try {
    $EmailCron = new EmailCron();
    $EmailCron->cron();
} catch (Exception $e) {
    echo $e->getMessage();
}
