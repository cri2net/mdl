<?php
require_once(__DIR__ . '/../config.php');

ShoppingCart::cron();

if (date('i') % 5 == 0) {
    // every 5 minutes
    try {
        $EmailCron = new EmailCron();
        $EmailCron->cron();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
