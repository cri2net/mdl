<?php
try {
    $response = [];
    $response['selected'] = '';

    $area = intval($_POST['depdrop_all_params']['area']);
    $plat = intval($_POST['depdrop_all_params']['service']);
    
    if (($area == 0) || ($plat == 0)) {
        $response['output'] = [];
    } else {
        $response['output'] = Budget::getFirmsList($area, $plat);
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
