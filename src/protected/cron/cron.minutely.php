<?php
if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
}

define('CRON_MODE', true);

require_once(__DIR__ . '/../config.php');
error_log(date('Y.m.d H:i:s') . "\r\n", 3, PROTECTED_DIR . '/logs/cron.minutely.starts.txt');
ShoppingCart::cron();

try {
    $EmailCron = new EmailCron();
    $EmailCron->cron();
} catch (Exception $e) {
    echo $e->getMessage();
}
