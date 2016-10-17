<?php
try {
    $response = [];
    $response['selected'] = '';

    $area = intval($_POST['depdrop_all_params']['area']);
    
    if ($area == 0) {
        $response['output'] = [];
    } else {
        $response['output'] = Budget::getPlatList($area);
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
