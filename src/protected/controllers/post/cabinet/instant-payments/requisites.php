<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    if (isset($_POST['get_last_step'])) {
        $id = $_SESSION['instant-payments-requisites']['requisites_last_payment_id'];
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
        $payment_id = $_payment['id'];

        if ($_payment) {
            $arr = ['go_to_payment_time' => microtime(true)];
            PDO_DB::update($arr, ShoppingCart::TABLE, $id);
            $_SESSION['instant-payments-requisites']['step'] = 'frame';
        } else {
            throw new Exception(ERROR_OLD_REQUEST);
        }

        return BASE_URL . '/cabinet/instant-payments/requisites/';
    }

    $lastname  = trim(stripslashes($_POST['lastname']));  // фамилия плательщика
    $firstname = trim(stripslashes($_POST['firstname'])); // имя плательщика
    $email     = trim($_POST['email']);                   // email плательщика
    $phone     = '+' . preg_replace('/[^0-9]/', '', $_POST['phone']); // Телефон
    
    $summ = trim($_POST['summ']); // сумма штрафа
    $summ = str_replace(',', '.', $summ);
    $summ = (double)$summ;
    $summ = (int)($summ * 100);


    $tmp_keys = [
        'email'     => 'E-mail',
        'firstname' => 'Ім’я',
        'inn'       => 'ІПН',
        'lastname'  => 'Прізвище',
        'address'   => 'Адреса',
        'phone'     => 'Телефон',
        'firme'     => 'Отримувач',
        'account'   => 'Розрахунковий рахунок',
        'bank'      => 'Банк отримувача',
        'dest'      => 'Призначення платежу',
        'mfo'       => 'МФО',
        'okpo'      => 'ЄДРПОУ',
    ];

    foreach ($tmp_keys as $key => $title) {
        $$key = trim(stripslashes($_POST[$key]));
        $_SESSION['instant-payments-requisites']['columns'][$key] = $$key;
    }
    
    foreach ($tmp_keys as $key => $title) {
        if (empty($$key) && !in_array($key, ['inn'])) {
            $err = str_ireplace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($err);
        }
    }

    if ($summ <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }

    $_SESSION['instant-payments-requisites']['step'] = 'details';


    if (!Authorization::isLogin()) {
        $user = User::getUserByEmail($_SESSION['instant-payments-requisites']['columns']['email']);

        if ($user !== null) {
            $user_id = $user['id'];
        } else {
            $user_id = User::registerFromPayment(
                $_SESSION['instant-payments-requisites']['columns']['email'],
                $_SESSION['instant-payments-requisites']['columns']['lastname'],
                $_SESSION['instant-payments-requisites']['columns']['name'],
                ''
            );
        }

    } else {
        $user_id = Authorization::getLoggedUserId();
    }

    $fio = "$lastname $firstname";

    $record = DirectPayments::pppCreatePayment($summ, $user_id, $fio, $address, $inn, $phone, $mfo, $bank, $account, $okpo, $dest, $firme);

    $record['payment_data'] = (array)(@json_decode($record['payment_data']));

    $TasLink = new TasLink('budget');
    $tas_session_id = $TasLink->initSession($record['id']);
    $TasLink->makePayment($record['summ_plat'], $record['summ_komis']);

    $_SESSION['instant-payments-requisites']['record_id'] = $record['id'];
    $_SESSION['instant-payments-requisites']['requisites_last_payment_id'] = $record['id'];

} catch (Exception $e) {
    $_SESSION['instant-payments-requisites']['step'] = 'region';
    $_SESSION['instant-payments-requisites']['status'] = false;
    $_SESSION['instant-payments-requisites']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/requisites/';
