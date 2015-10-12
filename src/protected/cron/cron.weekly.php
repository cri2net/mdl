<?php
require_once(__DIR__ . '/../config.php');

$start = microtime(true);

Street::cron();
House::cron();
Flat::cron();

echo "finish after " . (microtime(true) - $start) . " seconds\r\n";
