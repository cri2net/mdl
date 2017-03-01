<?php
$sections = ['dai', 'kindergarten', 'cards', 'phone', 'budget', 'cks', 'volia', 'secret-service'];
$current_section = (isset($__route_result['values']['section'])) ? ($__route_result['values']['section']) : false;

if ($current_section && in_array($current_section, $sections)) {
    $file = PROTECTED_DIR . '/scripts/cabinet/instant-payments/' . $current_section . '.php';
    if (file_exists($file)) {
        return require_once($file);
    }
}

require_once(PROTECTED_DIR . '/scripts/cabinet/quick-pays.php');
