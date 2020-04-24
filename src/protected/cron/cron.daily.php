<?php
require_once(__DIR__ . '/../config.php');

error_log(date('Y.m.d H:i:s') . "\r\n", 3, PROTECTED_DIR . '/logs/cron.daily.starts.txt');

ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

Street::cron();
House::cron();
Flat::cron();
