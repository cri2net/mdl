<?php

//require_once('protected/classes/Oschad.php');
$oschad = new Oschad();
$oschad->set_merchant($oschad_merchant_settings);
$oschad->set_order($totalAmountKop/100, str_pad($_payment['id'], 7, '0', STR_PAD_LEFT), 'Splata komunalnyh poslug');
//iconv('UTF-8','windows-1251//TRANSLIT','КП ГIОЦ');
$oschad->set_transaction('auth');
$oschad->sign($oschad_sign_key);

$processing_data = array(
    'first' => $oschad->get_fields(),
    'dates' => array(), // массив с ключами для requests
    'requests' => array() // массив с историей запросов
);

$update_data = array('processing_data' => json_encode($processing_data));
PDO_DB::update($update_data, ShoppingCart::TABLE, $_payment['id']);
