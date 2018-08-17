<?php

use cri2net\php_pdo_db\PDO_DB;

$oschad = new Oschad();
$processing_data = (array)(@json_decode($_payment['processing_data']));
$oschad_merchant_settings['BACKREF'] = BASE_URL . '/redirect-to-journal/?id=' . $processing_data['openid']->id;
$oschad->set_merchant($oschad_merchant_settings);

$oschad->set_order(round($totalAmountKop / 100, 2) . '', $_payment['id'], 'Splata komunalnyh poslug');
$oschad->set_transaction('auth');
$oschad->sign($oschad_sign_key);

$processing_data['first'] = $oschad->get_fields();
$processing_data['dates'] = [];    // массив с ключами для requests
$processing_data['requests'] = []; // массив с историей запросов

$update_data = ['processing_data' => json_encode($processing_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
PDO_DB::update($update_data, ShoppingCart::TABLE, $_payment['id']);
