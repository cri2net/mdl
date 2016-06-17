<?php
if (!isset($_POST) || !isset($_POST['Function'])) {
    header('Location: ' . BASE_URL . '/cabinet/payments/history/');
    exit('$_POST[Function] not set');
}

if (!isset($_POST['Result'])) {
    die('$_POST[Result] not set');
}

if ($_POST['Function'] == 'TransResponse') {
    $rcdesc = (isset($_POST['RC'])) ? Oschad::getRCDesciption($_POST['RC']) : '';
    Oschad::logPaysys('oschad_mycard', 'POST ' . $rcdesc, var_export($_POST, true));

    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_POST['Order']);
    if ($_payment === null) {
        throw new Exception("Unknow OrderID {$_POST['Order']}");
        exit();
    }
    $date = date('d-m-Y H:i:s');
    switch ($_POST['Result']) {
        case 0:
            $processing_data = (array)(@json_decode($_payment['processing_data']));
            $processing_data['requests'] = (array)$processing_data['requests'];
            $processing_data['dates'][] = $date;
            $processing_data['requests'][$date] = $_POST;
            $to_update = [
                'processing_data' => json_encode($processing_data),
                'send_payment_status_to_reports' => 0
            ];
            $to_update['status'] = ($_POST['TRTYPE'] == '24') ? 'reverse' : 'success';
            break;

        default:
            $to_update['status'] = 'error';
    }

    PDO_DB::update($to_update, ShoppingCart::TABLE, $_payment['id']);
    ShoppingCart::send_payment_status_to_reports($_payment['id']);
}