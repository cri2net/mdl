<?php
require_once(__DIR__ . '/protected/config.php');

$file = ROOT . '/protected/controllers/' . $__route_result['controller'] . '.php';
if (file_exists($file)) {
    require_once($file);
}
