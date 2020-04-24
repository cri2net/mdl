<?php

$action = basename($__route_result['values']['action']);
$file = PROTECTED_DIR . '/ajax/' . $__route_result['action'] . '/' . $action. '.php';

header('Content-Type: application/json');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");              // дата в прошлом
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // всегда модифицируется
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                    // HTTP/1.0

if (file_exists($file)) {
    require_once($file);
}
