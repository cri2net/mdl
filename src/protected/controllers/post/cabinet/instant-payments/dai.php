<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    if (isset($_POST['get_last_step'])) {
        $id = $_SESSION['instant-payments-dai']['dai_last_payment_id'];
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
        $payment_id = $_payment['id'];

        if ($_payment) {
            $arr = ['go_to_payment_time' => microtime(true)];
            PDO_DB::update($arr, ShoppingCart::TABLE, $id);
            $_SESSION['instant-payments-dai']['step'] = 'frame';
        } else {
            throw new Exception(ERROR_OLD_REQUEST);
        }

        return BASE_URL . '/cabinet/instant-payments/dai/';
    }


    $regions = Gai::getRegions();

    $tmp_keys = [
        'region'                  => 'Область',
        'postanova_series'        => 'Серія постанови',
        'postanova_number'        => 'Номер постанови',
        'protocol_date'           => 'Дата постанови',
        'protocol_summ'           => 'Сума штрафу',
        'penalty_user_lastname'   => 'Прізвище платника',
        'penalty_user_name'       => 'Ім’я платника',
        'penalty_user_fathername' => 'По-батькові платника',
        'penalty_user_address'    => 'Адреса платника',
        'penalty_user_email'      => 'E-Mail',
    ];

    foreach ($tmp_keys as $key => $title) {
        $$key = trim(stripslashes($_POST[$key]));
        $_SESSION['instant-payments-dai']['columns'][$key] = $$key;
    }
    
    foreach ($tmp_keys as $key => $title) {
        if (empty($$key) && !in_array($key, ['penalty_user_fathername', 'protocol_date'])) {
            $err = str_ireplace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($err);
        }
    }

    $protocol_summ = str_replace(',', '.', $protocol_summ);
    $protocol_summ = (double)$protocol_summ;
    $protocol_summ = (int)($protocol_summ * 100);
    if ($protocol_summ <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    if (!filter_var($penalty_user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }
    
    $region_ok = false;
    foreach ($regions as $item) {
        if ($item['ID_AREA'] == $region) {
            $region_ok = true;
        }
    }

    if (!$region_ok) {
        throw new Exception('Область не вказана');
    }

    $_SESSION['instant-payments-dai']['step'] = 'details';

    if (!Authorization::isLogin()) {
        $user = User::getUserByEmail($_SESSION['instant-payments-dai']['columns']['penalty_user_email']);

        if ($user !== null) {
            $user_id = $user['id'];
        } else {
            $user_id = User::registerFromPayment(
                $_SESSION['instant-payments-dai']['columns']['penalty_user_email'],
                $_SESSION['instant-payments-dai']['columns']['penalty_user_lastname'],
                $_SESSION['instant-payments-dai']['columns']['penalty_user_name'],
                $_SESSION['instant-payments-dai']['columns']['penalty_user_fathername']
            );
        }

    } else {
        $user_id = Authorization::getLoggedUserId();
    }

    $fio = "$penalty_user_lastname $penalty_user_name $penalty_user_fathername";
    $record = Gai::set_request_to_ppp($region, $protocol_summ, $user_id, $fio, $penalty_user_address, '', '', '', '', $postanova_series, $postanova_number, '', $protocol_date);
    
    $_SESSION['instant-payments-dai']['record_id'] = $record['id'];
    $_SESSION['instant-payments-dai']['dai_last_payment_id'] = $record['id'];
    
    if ($record['processing'] == 'tas') {
        $TasLink = new TasLink('budget');
        $tas_session_id = $TasLink->initSession($record['id']);
        $TasLink->makePayment($record['summ_plat'], $record['summ_komis']);
    }

} catch (Exception $e) {
    $_SESSION['instant-payments-dai']['step'] = 'region';
    $_SESSION['instant-payments-dai']['status'] = false;
    $_SESSION['instant-payments-dai']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/dai/';
