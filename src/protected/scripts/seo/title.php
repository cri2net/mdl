<?php
$_lSEO = 'TITLES';

switch ($__route_result['controller'] . "/" . $__route_result['action']) {
    case 'page/cabinet':
        switch ($__route_result['values']['subpage']) {
            case 'registration':
                $seo_str = getTextVariableValueByName($_lSEO . '_REGISTRATION');
                break;

            default:
                $seo_str = getTextVariableValueByName($_lSEO . '_CABINET');
        }
        break;

    case 'error/404':
        $seo_str = "ЦКС — Помилка 404";
        break;
}

if ($seo_str == '') {
    $seo_str = 'ЦКС';
}

echo htmlspecialchars($seo_str, ENT_QUOTES);
