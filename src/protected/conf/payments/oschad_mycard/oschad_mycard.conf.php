<?php
$oschad_merchant_settings = [
    'CURRENCY'   => 'UAH',
    'MERCH_NAME' => 'GIOC KIEV 2',
    'MERCH_URL'  => 'gioc.kiev.ua',
    'MERCHANT'   => '20904509',
    'TERMINAL'   => '20907449',
    'EMAIL'      => 'mistakes@gioc.kiev.ua',
    'COUNTRY'    => 'UA',
    'MERCH_GMT'  => '+2',
    'BACKREF'    => BASE_URL . '/payments-notify/oschad_mycard/'
];

$payment_form_action = 'https://3ds.oschadnybank.com/cgi-bin/cgi_link';
$oschad_sign_key = '0089e7e9a1abca22ae2d621a050d8966'; //тестовый, поменять на боевой
$payment_form_target = '_self';
