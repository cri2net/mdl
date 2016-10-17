<?php
try {
    $response = [];
    $response['selected'] = '';

    $state = intval($_POST['depdrop_all_params']['state']);
    
    if ($state == 0) {
        $response['output'] = [];
    } else {
        $response['output'] = Budget::getAreas($state);
        $response['selected'] = @$response['output'][0]['id'];
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
