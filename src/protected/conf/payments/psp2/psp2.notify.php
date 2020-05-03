<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    $rawBody = file_get_contents('php://input');
    $data = json_decode($rawBody ? : '', true);

    if (empty($data)) {
        $data = $_POST;
    }

    $mess = date('d-m-Y H:i:s')."\r\n";
    $mess .= "data: ".var_export($data, true)."\r\n\r\n\r\n\r\n";

    $dir = PROTECTED_DIR . '/logs/paysystems/psp2/' . date('Y/m/');
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = $dir . 'notify.txt';
    error_log($mess, 3, $file);

    $payment_id = $data['order_id'];
    $success = in_array($data['status'], ['success']);
    $arr = [
        'status'                 => ($success) ? 'success' : 'error',
        'processing'             => 'psp2',
        'reports_id_plat_klient' => $data['transaction_id'],
        'go_to_payment_time'     => microtime(true),
        'reports_id_pack'        => $data['receipt_no'],
    ];
    if ($data['status'] == 'in_processing') {
        $arr['status'] = 'pending';
    }

    $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);

    if (isset($data['total']) && !empty($data['total'])) {
        $arr['summ_total'] = $data['total'] / 100;
        $arr['summ_komis'] = $arr['summ_total'] - $payment['summ_plat'];
    }

    if (($payment['status'] != 'success') || ($arr['status'] != 'error')) {
        
        PDO_DB::update($arr, ShoppingCart::TABLE, $payment_id);
        ShoppingCart::sendFirstPDF($payment_id);
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response = [
        'success'   => false,
        'error'     => $e->getMessage(),
        'exception' => $e,
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
