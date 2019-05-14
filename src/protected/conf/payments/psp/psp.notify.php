<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $mess = date('d-m-Y H:i:s')."\r\n";
    $mess .= "HTTP_RAW_POST_DATA: ".var_export(@$HTTP_RAW_POST_DATA, true)."\r\n";
    $mess .= "POST: ".var_export(@$_POST, true)."\r\n\r\n\r\n\r\n";

    $dir = PROTECTED_DIR . '/logs/paysystems/psp/' . date('Y/');
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    $file = $dir . 'notify.txt';
    error_log($mess, 3, $file);

    // Проверить подпись:
    // $_POST['sign'] & $_POST['data'];

    $data = json_decode($_POST['data'], true);

    $payment_id = $data['order_id'];
    $arr = [
        'status'                         => (in_array($data['status'], ['success', '1', 'true', true, 1]) ? 'success' : 'error'),
        'reports_id_plat_klient'         => $data['payment_id'],
        'reports_id_pack'                => $data['id_pack'],
        'processing'                     => 'psp',
        'send_payment_to_reports'        => 1,
        'go_to_payment_time'             => microtime(true),
        'send_payment_status_to_reports' => 1,
    ];

    $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);

    if (isset($data['total_amount']) && !empty($data['total_amount'])) {
        $arr['summ_total'] = $data['total_amount'] / 100;
        $arr['summ_komis'] = $arr['summ_total'] - $payment['summ_plat'];
    }

    if (($payment['status'] != 'success') || ($arr['status'] != 'error')) {
        
        PDO_DB::update($arr, ShoppingCart::TABLE, $payment_id);
        ShoppingCart::sendFirstPDF($payment_id);
    }

    $processing_data = @json_decode($payment['processing_data'], true);

    $data = [
        'status' => true,
        'link'   => BASE_URL . '/redirect-to-journal/?id=' . $processing_data['openid']['id'],
    ];
    $Encryptor = new Encryptor(Psp::PSP_PRIVATE_KEY, Psp::PSP_PUBLIC_KEY);
    $response = [
        'data' => json_encode($data),
    ];
    $response['sign'] = $Encryptor->get_sign($response['data']);

} catch (Exception $e) {
    $response = [
        'success'   => false,
        'error'     => $e->getMessage(),
        'exception' => $e,
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
