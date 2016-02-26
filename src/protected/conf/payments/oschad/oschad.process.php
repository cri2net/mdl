<?php

//require_once('protected/classes/Oschad.php');
$oschad = new Oschad();
$oschad->set_merchant($oschad_merchant_settings);
$oschad->set_order($totalAmountKop, $_payment['id'], 'Оплата комунальних послуг');
$oschad->set_transaction('auth');
$oschad->sign($oschad_sign_key);

$processing_data = array(
    'first' => $oschad->get_fields(),
    'dates' => array(), // массив с ключами для requests
    'requests' => array() // массив с историей запросов
);

$update_data = array('processing_data' => json_encode($processing_data));
PDO_DB::update($update_data, ShoppingCart::TABLE, $_payment['id']);
