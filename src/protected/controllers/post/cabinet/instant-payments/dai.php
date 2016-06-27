<?php
try {

    if (!Authorization::isLogin()) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $regions = Gai::getRegions();
    $Gai = new Gai();

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

    $fio = "$penalty_user_lastname $penalty_user_name $penalty_user_fathername";
    $user_id = Authorization::getLoggedUserId();
    $record = $Gai->set_request_to_ppp($error_str, $region, $protocol_summ, $user_id, $fio, $penalty_user_address, '', '', '', '', $postanova_series, $postanova_number, '', $protocol_date);
    
    if ($record == false) {
        $err[] = $error_str;
    } else {
        $_SESSION['instant-payments-dai']['dai_last_payment_id'] = $record['id'];
    }
    
    if ($record['processing'] == 'tas') {
        $TasLink = new TasLink('budget');
        $tas_session_id = $TasLink->initSession('gioc-' . $record['id']);
        $TasLink->makePayment($record['summ_plat'] / 100, $record['summ_komis'] / 100);
    }

    // суммы в копейках, переводим в гривны:
    $record['summ_plat'] = number_format($record['summ_plat'] / 100, 1);
    $record['summ_komis'] = number_format($record['summ_komis'] / 100, 1);
    $record['summ_total'] = number_format($record['summ_total'] / 100, 1);

    $record['summ_plat'] .= (substr($record['summ_plat'], strlen($record['summ_plat']) - 2) == '.0') ? '0' : '';
    $record['summ_komis'] .= (substr($record['summ_komis'], strlen($record['summ_komis']) - 2) == '.0') ? '0' : '';
    $record['summ_total'] .= (substr($record['summ_total'], strlen($record['summ_total']) - 2) == '.0') ? '0' : '';

    $_SESSION['instant-payments-dai']['record_id'] = $record['id'];

} catch (Exception $e) {
    $_SESSION['instant-payments-dai']['step'] = 'region';
    $_SESSION['instant-payments-dai']['status'] = false;
    $_SESSION['instant-payments-dai']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/instant-payments/dai/';
