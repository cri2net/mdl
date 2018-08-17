<?php
require_once(__DIR__ . '/../config.php');
error_log(date('Y.m.d H:i:s') . "\r\n", 3, PROTECTED_DIR . '/logs/cron.weekly.starts.txt');
