<?php

require_once(__DIR__ . '/protected/config.php');

ob_start();

$file = ROOT . '/protected/controllers/' . $__route_result['controller'] . '.php';
if (file_exists($file)) {
    require_once($file);
}

$out = ob_get_clean();

if (in_array($__route_result['controller'], ['page', 'error'])) {
    if (!$me->is_local) {
        $out = Html::clear($out);
    }
}
Http::gzip($out);
