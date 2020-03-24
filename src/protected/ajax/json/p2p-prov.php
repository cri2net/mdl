<?php
try {
    $response = ['status' => true];

    $additional = [
        'p_value'      => $_POST['code'],
        'p_cell_phone' => Validator::makeRightPhone($_POST['phone']),
    ];
    KmdaP2P::confirm($_POST['payment_id'], $additional);

    KmdaOrders::setOrderStatus($_POST['payment_id']);

    $response['url'] = BASE_URL . '/p2p-payment-status/' . $_POST['payment_id'] . '/';

} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}


echo json_encode($response, JSON_UNESCAPED_UNICODE);
