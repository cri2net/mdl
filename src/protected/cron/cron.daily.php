<?php
require_once(__DIR__ . '/../config.php');

error_log(date('Y.m.d H:i:s') . "\r\n", 3, PROTECTED_DIR . '/logs/cron.daily.starts.txt');

ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

if (date('d') == '01') {
    $first_day = new DateTime('first day of previous month 00:00:00');
} else {
    $first_day = new DateTime('first day of this month 00:00:00');
}

Street::cron();
Authorization::cron();

$today = new DateTime('today');
CronTasks::sendReportAboutTasLink(date_timestamp_get($first_day), date_timestamp_get($today) - 1);
