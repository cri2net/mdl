<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    if (isset($_POST['get_last_step'])) {
        $id = $_SESSION['instant-payments-kinders']['kinders_last_payment_id'];
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);

        if ($_payment) {
            $arr = ['go_to_payment_time' => microtime(true)];
            PDO_DB::update($arr, ShoppingCart::TABLE, $id);
            $_SESSION['instant-payments-kinders']['step'] = 'frame';
        } else {
            throw new Exception(ERROR_OLD_REQUEST);
        }

        return BASE_URL . '/cabinet/instant-payments/kindergarten/';
    }


    $districts = Kinders::getFirmeList();
    $kindergartens_list = [];

    foreach ($districts as $item) {
       
        $tmp = Kinders::getInstitutionList($item['id']);
       
        for ($i=0; $i < count($tmp); $i++) {
            $kindergarten = $tmp[$i];
            $kindergarten['IDAREA'] = 233;
            $kindergarten['ID_DISTRICT'] = $item['id'];
            $kindergarten['FIRME'] = $item['id'];
            
            $kindergartens_list[] = $kindergarten;
        }
    }

    $tmp_keys = [
        'summ'                    => 'Сума',
        'penalty_user_lastname'   => 'Прізвище платника',
        'penalty_user_name'       => 'Ім’я платника',
        'penalty_user_fathername' => 'По-батькові платника',
        'penalty_user_address'    => 'Адреса платника',
        'penalty_user_email'      => 'E-Mail',
        'child_fio'               => 'Прізвище учня',
        'child_class'             => 'Група / Клас учня',
        'id_district'             => 'Район',
    ];

    foreach ($tmp_keys as $key => $title) {
        $$key = trim(stripslashes($_POST[$key]));
        $_SESSION['instant-payments-kinders']['columns'][$key] = $$key;
    }

    foreach ($tmp_keys as $key => $title) {
        if (empty($$key) && !in_array($key, ['penalty_user_fathername'])) {
            $err = str_ireplace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($err);
        }
    }

    $summ = str_replace(',', '.', $summ);
    $summ = (double)$summ;
    $summ = (int)($summ * 100);
    if ($summ <= 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    if (!filter_var($penalty_user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }

    $real_child_fio = Kinders::getChildrenList($child_class, $child_fio);
    if (is_array($real_child_fio)) {
        $child_fio = $real_child_fio['list'][0]['id'];
    }
    
    $kindergarten_ok = false;
    foreach ($kindergartens_list as $item) {
        if ($item['R101'] == $kindergarten['R101']) {
            $kindergarten_item = $item;
            $kindergarten_ok = true;
        }
    }

    if (!$kindergarten_ok) {
        throw new Exception('Установа не вказана');
    }

    $_SESSION['instant-payments-kinders']['step'] = 'details';
    $_SESSION['instant-payments-kinders']['columns']['R101'] = $kindergarten['R101'];
    $_SESSION['instant-payments-kinders']['columns']['id_district'] = intval($kindergarten['ID_DISTRICT']);

    if (!Authorization::isLogin()) {
        $user = User::getUserByEmail($_SESSION['instant-payments-kinders']['columns']['penalty_user_email']);

        if ($user !== null) {
            $user_id = $user['id'];
        } else {
            $user_id = User::registerFromPayment(
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_email'],
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_lastname'],
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_name'],
                $_SESSION['instant-payments-kinders']['columns']['penalty_user_fathername']
            );
        }

    } else {
        $user_id = Authorization::getLoggedUserId();
    }
    
    $fio = "$penalty_user_lastname $penalty_user_name $penalty_user_fathername";
    
    $record = Kinders::pppCreatePayment(
        $kindergarten_item['IDAREA'],
        $kindergarten_item['FIRME'],
        $summ,
        $user_id,
        $fio,
        $penalty_user_address,
        $kindergarten_item['R101'],
        $child_class,
        $child_fio
    );
    $_SESSION['instant-payments-kinders']['kinders_last_payment_id'] = $record['id'];

    if ($record['processing'] == 'tas') {
        $TasLink = new TasLink('budget');
        $tas_session_id = $TasLink->initSession($record['id']);
        $TasLink->makePayment($record['summ_plat'], $record['summ_komis']);
    }

    $_SESSION['instant-payments-kinders']['record_id'] = $record['id'];

} catch (Exception $e) {
    $_SESSION['instant-payments-kinders']['step'] = 'region';
    $_SESSION['instant-payments-kinders']['status'] = false;
    $_SESSION['instant-payments-kinders']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/kindergarten/';
