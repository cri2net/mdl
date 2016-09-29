<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    $firme_id = $_POST['firme'];
    $sum = 0;
    $plat_list = [];

    $_POST['items'] = (array)$_POST['items'];

    foreach ($_POST['items'] as $item) {

        $item = str_replace('inp_', '', $item);
        $item_sum = floatval(str_replace(',', '.', $_POST['inp_' . $item . '_sum']));

        $plat_list[] = [
            'id'  => $item,
            'sum' => $item_sum,
        ];

        $sum += $item_sum;
    }

    if ($sum <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    if (Authorization::isLogin()) {
        $user_id = Authorization::getLoggedUserId();
        $user = User::getUserById($user_id);
        $fio = "{$user['lastname']} {$user['name']} {$user['fathername']}";
    } else {
        $user_id = 1;
        $fio = 'cks';
    }
    $address = 'Київ';
    
    $_payment = CKS::createPayment($plat_list, $firme_id, round($sum * 100), $fio, $address);
    $_SESSION['instant-payments-cks']['cks_last_payment_id'] = $_payment['id'];

    if ($_payment['processing'] == 'tas') {
        $TasLink = new TasLink('other');
        $tas_session_id = $TasLink->initSession($_payment['id']);
        $TasLink->makePayment($_payment['summ_plat'], $_payment['summ_komis']);
    }

    $_SESSION['instant-payments-cks']['record_id'] = $_payment['id'];

    if ($_payment) {
        $arr = ['go_to_payment_time' => microtime(true)];
        PDO_DB::update($arr, ShoppingCart::TABLE, $id);
        $_SESSION['instant-payments-cks']['step'] = 'frame';
    }

} catch (Exception $e) {
    $_SESSION['instant-payments-cks']['step'] = 'region';
    $_SESSION['instant-payments-cks']['status'] = false;
    $_SESSION['instant-payments-cks']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/cks/';
