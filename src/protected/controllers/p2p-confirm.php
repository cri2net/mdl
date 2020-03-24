<?php

use cri2net\php_pdo_db\PDO_DB;

$mess = date('d-m-Y H:i:s')."\r\n";
$mess .= "POST: ".var_export(@$_POST, true)."\r\n";
$mess .= "GET: ".var_export(@$_GET, true)."\r\n\r\n\r\n\r\n";

$file = PROTECTED_DIR . '/logs/p2p_confirm_log.txt';
@error_log($mess, 3, $file);

if (empty($_GET['id'])) {
    Http::redirect(BASE_URL);
}

$payment = PDO_DB::row_by_id(TABLE_PREFIX . 'payment', $_GET['id']);

if (!$payment || ($payment['type'] != 'p2p')) {
    Http::redirect(BASE_URL);
}

try {
    $data = [
        'p_PaRes_1' => $_POST['PaRes'],
        'p_md'      => $_POST['MD'],
    ];
    KmdaP2P::confirm($payment['id'], $data);
    Http::redirect(BASE_URL . '/p2p-payment-status/' . $payment['id'] . '/');

} catch (Exception $e) {
}

Http::redirect(BASE_URL);
