<?php
$oschad_merchant_settings = [
    'CURRENCY'   => 'UAH',
    'MERCH_NAME' => iconv('UTF-8', 'windows-1251//TRANSLIT', 'gioc.kiev.ua'),
    'MERCH_URL'  => 'gioc.kiev.ua',
    'MERCHANT'   => '20904292',
    'TERMINAL'   => '20907201',
    'EMAIL'      => 'mistakes@gioc.kiev.ua',
    'COUNTRY'    => 'UA',
    'MERCH_GMT'  => '+2',
    'BACKREF'    => BASE_URL . '/payments-notify/oschad/'
];
$payment_form_action = 'https://3ds.oschadnybank.com/cgi-bin/cgi_link';
$oschad_sign_key = '58102f462425c6e829ac878e6dd0ea61';
$payment_form_target = '_self';
