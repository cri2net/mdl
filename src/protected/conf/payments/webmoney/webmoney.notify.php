<?php

use cri2net\php_pdo_db\PDO_DB;

$mess = date('d-m-Y H:i:s')."\r\n";
$mess .= "\$_POST= ".var_export(@$_POST, true).";\r\n\r\n";

$dir = PROTECTED_DIR . "/logs/paysystems/$paysystem/";
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}
$file = $dir . 'log_notify.txt';
error_log($mess, 3, $file);

try {
    if (empty($_POST)) {
        throw new Exception("No post data");
    }

    if (!empty($_POST['LMI_PAYER_PURSE']) && $_POST['LMI_PREREQUEST'] == 1) {
        exit("YES");
    }

    // WebMoney::checkSignature($_POST);

    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_POST['LMI_PAYMENT_NO']);
    if (!$_payment) {
        throw new Exception(ERROR_GET_PAYMENT);
    }
    
    $processing_data = (array)(@json_decode($_payment['processing_data']));
    $processing_data['requests'] = (array)$processing_data['requests'];
    $processing_data['dates'][] = $date;
    $processing_data['requests'][$date] = $_POST;

    $to_update = [
        'processing_data'                => json_encode($processing_data),
        'send_payment_status_to_reports' => 0,
    ];

    $success = (!empty($_POST['LMI_PAYER_PURSE']));
    if ($success) {
        $to_update['status'] = 'success';
    } else {
        $to_update['status'] = 'error';
    }

    PDO_DB::update($to_update, ShoppingCart::TABLE, $_payment['id']);
    ShoppingCart::send_payment_status_to_reports($_payment['id']);

    exit("YES");

} catch (Exception $e) {
    echo $e->getMessage();
}
