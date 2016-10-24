<?php
try {
    $response = ['success' => true];
    $response['list'] = Flat::getTenantList($_GET['flat_id']);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(
        [
            'success'           => false,
            'error_code'        => $e->getCode(),
            'error_description' => $e->getMessage(),
        ],
        JSON_UNESCAPED_UNICODE
    );
}
