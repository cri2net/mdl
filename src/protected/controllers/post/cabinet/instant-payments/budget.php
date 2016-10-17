<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    if (isset($_POST['get_last_step'])) {
        $id = $_SESSION['instant-payments-budget']['budget_last_payment_id'];
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);

        if ($_payment) {
            $arr = ['go_to_payment_time' => microtime(true)];
            PDO_DB::update($arr, ShoppingCart::TABLE, $id);
            $_SESSION['instant-payments-budget']['step'] = 'frame';
        } else {
            throw new Exception(ERROR_OLD_REQUEST);
        }

        return BASE_URL . '/cabinet/instant-payments/budget/';
    }

    $state   = (int)$_POST['state'];   // регион (область)
    $area    = (int)$_POST['area'];    // район
    $service = (int)$_POST['service']; // услуга
    $firme   = (int)$_POST['firme'];   // получатель

    $lastname = trim(stripslashes($_POST['lastname'])); // фамилия плательщика
    $name     = trim(stripslashes($_POST['name']));     // имя плательщика
    $inn      = trim(stripslashes($_POST['inn']));      // ИНН плательщика
    $address  = trim(stripslashes($_POST['address']));  // адрес плательщика
    $email    = trim($_POST['email']);                  // email плательщика
    
    $summ = trim($_POST['summ']); // сумма штрафа
    $summ = str_replace(',', '.', $summ);
    $summ = (double)$summ;
    $summ = (int)($summ * 100);


    $tmp_keys = [
        'summ'     => 'Сума',
        'lastname' => 'Прізвище платника',
        'name'     => 'Ім’я платника',
        'address'  => 'Адреса платника',
        'email'    => 'E-Mail',
        'inn'      => 'ІПН',
    ];

    foreach ($tmp_keys as $key => $title) {
        $$key = trim(stripslashes($_POST[$key]));
        $_SESSION['instant-payments-budget']['columns'][$key] = $$key;
    }

    foreach ($tmp_keys as $key => $title) {
        if (empty($$key) && !in_array($key, ['penalty_user_fathername'])) {
            $err = str_ireplace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($err);
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }
    if ($summ <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }
    if ($firme <= 0) {
        throw new Exception('Отримувача платежу не вказано');
    }

    $_SESSION['instant-payments-budget']['step'] = 'details';

    $fio = "$lastname $name";

    if (!Authorization::isLogin()) {
        $user = User::getUserByEmail($_SESSION['instant-payments-budget']['columns']['email']);

        if ($user !== null) {
            $user_id = $user['id'];
        } else {
            $user_id = User::registerFromPayment(
                $_SESSION['instant-payments-budget']['columns']['email'],
                $_SESSION['instant-payments-budget']['columns']['lastname'],
                $_SESSION['instant-payments-budget']['columns']['name'],
                $_SESSION['instant-payments-budget']['columns']['']
            );
        }

    } else {
        $user_id = Authorization::getLoggedUserId();
    }

    $record = Budget::createPayment($area, $firme, $service, $summ * 100, $user_id, $fio, $inn, $address);
    $record['payment_data'] = (array)(@json_decode($record['payment_data']));
    $_SESSION['direct_last_payment_id'] = $record['id'];
    $_SESSION['instant-payments-budget']['budget_last_payment_id'] = $record['id'];

    $TasLink = new TasLink('budget');
    $tas_session_id = $TasLink->initSession($record['id']);
    $TasLink->makePayment($record['summ_plat'], $record['summ_komis']);

    $_SESSION['instant-payments-kinders']['record_id'] = $record['id'];

} catch (Exception $e) {
    $_SESSION['instant-payments-budget']['step'] = 'region';
    $_SESSION['instant-payments-budget']['status'] = false;
    $_SESSION['instant-payments-budget']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/budget/';
