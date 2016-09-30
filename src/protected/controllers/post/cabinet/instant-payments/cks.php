<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    $firme_id = $_POST['firme'];
    $sum = 0;
    $sum_str = '';
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
        $sum_str .= round($item_sum * 100) . ',';
    }

    if ($sum <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    $sum_str = trim($sum_str, ',');

    $tmp_keys = [
        'penalty_user_lastname'   => 'Прізвище',
        'penalty_user_name'       => 'Ім’я',
        'penalty_user_fathername' => 'По-батькові',
        'penalty_user_address'    => 'Адреса',
        'penalty_user_email'      => 'E-Mail',
    ];

    foreach ($tmp_keys as $key => $title) {
        $$key = trim(stripslashes($_POST[$key]));
        $_SESSION['instant-payments-cks']['columns'][$key] = $$key;
    }

    foreach ($tmp_keys as $key => $title) {
        if (empty($$key) && !in_array($key, ['penalty_user_fathername'])) {
            $err = str_ireplace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($err);
        }
    }


    if (!Authorization::isLogin()) {
        $user = User::getUserByEmail($_SESSION['instant-payments-cks']['columns']['penalty_user_email']);

        if ($user !== null) {
            $user_id = $user['id'];
        } else {
            $user_id = User::registerFromPayment(
                $_SESSION['instant-payments-cks']['columns']['penalty_user_email'],
                $_SESSION['instant-payments-cks']['columns']['penalty_user_lastname'],
                $_SESSION['instant-payments-cks']['columns']['penalty_user_name'],
                $_SESSION['instant-payments-cks']['columns']['penalty_user_fathername']
            );
        }

    } else {
        $user_id = Authorization::getLoggedUserId();
    }

    $fio = "$penalty_user_lastname $penalty_user_name $penalty_user_fathername";

    $_payment = CKS::createPayment($plat_list, $firme_id, $sum_str, $fio, $penalty_user_address, $user_id);
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
