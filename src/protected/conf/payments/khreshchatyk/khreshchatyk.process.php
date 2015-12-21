<?php

$processing_data = [
    'first' => [
        'card_id' => $card['id'],
        'acc_bank' => $card['additional']['acc_bank'],
    ],
    'requests' => []
];

$cdata = [
    'processing_data' => json_encode($processing_data),
];

PDO_DB::updateWithWhere($cdata, ShoppingCart::TABLE, "id='{$_payment['id']}' AND user_id='$user_id'");

$khreshchatyk = new Khreshchatyk();
$khreshchatyk->makePayment($_payment['id']);
ShoppingCart::send_payment_status_to_reports($_payment['id']);
