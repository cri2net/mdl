<?php
require_once(PROTECTED_DIR . '/layouts/_header.php');

$file = PROTECTED_DIR . '/pages/' . $__route_result['action'] . '.php';

if (file_exists($file)) {
    require_once($file);
} elseif (isset($__route_result['values']['subpage'])) {
    $subpage = basename($__route_result['values']['subpage']);
    $file = PROTECTED_DIR . "/pages/{$__route_result['action']}/$subpage.php";

    if (file_exists($file)) {
        require_once($file);
    }
} else {
    $file = PROTECTED_DIR . "/pages/{$__route_result['action']}/index.php";

    if (file_exists($file)) {
        require_once($file);
    }
}

require_once(PROTECTED_DIR . '/layouts/_footer.php');
