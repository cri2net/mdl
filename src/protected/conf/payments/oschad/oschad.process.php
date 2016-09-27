<?php

use cri2net\php_pdo_db\PDO_DB;

$oschad = new Oschad();
$oschad_merchant_settings['BACKREF'] = 'https://www.gioc.kiev.ua/payment-status/' . $_payment['id'] . '/';
$oschad->set_merchant($oschad_merchant_settings);

$oschad->set_order(round($totalAmountKop/100,2), str_pad($_payment['id'], 6, '0', STR_PAD_LEFT), 'Splata komunalnyh poslug');
$oschad->set_transaction('auth');
$oschad->sign($oschad_sign_key);

$processing_data = [
    'first'    => $oschad->get_fields(),
    'dates'    => [], // массив с ключами для requests
    'requests' => []  // массив с историей запросов
];

$update_data = ['processing_data' => json_encode($processing_data)];
PDO_DB::update($update_data, ShoppingCart::TABLE, $_payment['id']);
