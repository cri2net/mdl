<?php

use cri2net\php_pdo_db\PDO_DB;

$date = date('d-m-Y H:i:s');
$mess = "$date\r\nPOST: ".var_export(@$_POST, true)."\r\n\r\n\r\n";

$dir = PROTECTED_DIR . "/logs/paysystems/$paysystem/";
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}
$file = $dir . 'log_notify.txt';
error_log($mess, 3, $file);

$request = @json_decode($HTTP_RAW_POST_DATA);
$TasLink = new TasLink();

if ($request == null) {
    exit('Not valid JSON');
}

if (!$TasLink->checkNotifySignature($request)) {
    exit('Not valid signature');
}

$payment_id = str_replace('gioc-', '', $request->TRANID);
$_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
if (!$_payment) {
    exit('Unknown payment');
}

$response = [];
if ($request->APPROVAL == 'Отсутствует') {
    $request->APPROVAL = '';
}

$processing_data = (array)(@json_decode($_payment['processing_data']));
$processing_data['requests'] = (array)$processing_data['requests'];
$processing_data['dates'][] = $date;
$processing_data['requests'][$date] = $request;

$to_update = [
    'processing_data' => json_encode($processing_data),
    'send_payment_status_to_reports' => 0
];

$success = in_array($request->RESPCODE, ['000', '001']);
if ($success) {
    $to_update['status'] = 'success';
} else {
    $to_update['status'] = 'error';
}

PDO_DB::update($to_update, ShoppingCart::TABLE, $_payment['id']);
ShoppingCart::send_payment_status_to_reports($_payment['id']);

$response['forwardUrl'] = BASE_URL . "/payment-status/{$payment_id}/";
echo json_encode($response);
