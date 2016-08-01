<?php
require_once(__DIR__ . '/../config.php');

ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

if (date('d') == '01') {
    $first_day = new DateTime('first day of previous month 00:00:00');
} else {
    $first_day = new DateTime('first day of this month 00:00:00');
}

$today = new DateTime('today');
CronTasks::sendReportAboutTasLink(date_timestamp_get($first_day), date_timestamp_get($today) - 1);

Authorization::cron();
