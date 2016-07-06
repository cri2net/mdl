<?php
require_once(__DIR__ . '/../config.php');

$yesterday = new DateTime('yesterday');
$today = new DateTime('today');
CronTasks::sendReportAboutTasLink(date_timestamp_get($yesterday), date_timestamp_get($today) - 1);

Authorization::cron();
