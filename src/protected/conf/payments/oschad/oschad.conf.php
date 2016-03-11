<?php
$oschad_merchant_settings = [
    'CURRENCY'   => 'UAH',
    'MERCH_NAME' => iconv('UTF-8','windows-1251//TRANSLIT','gioc.kiev.ua'),
    'MERCH_URL'  => 'gioc.kiev.ua',
    'MERCHANT'   => '20904292',
    'TERMINAL'   => '20907201',
    'EMAIL'      => 'mistakes@gioc.kiev.ua',
    'COUNTRY'    => 'UA',
    'MERCH_GMT'  => '+2',
    'BACKREF'    => 'https://www.gioc.kiev.ua/payments-notify/oschad/'
];
$payment_form_action = 'https://3ds.oschadnybank.com/cgi-bin/cgi_link';
$oschad_sign_key = '58102f462425c6e829ac878e6dd0ea61';
$payment_form_target = '_self';

function switch_to_merchant($merch_index)
{
    global $oschad_merchant_settings, $oschad_sign_key;

    $osc_merch = [
        2 => [
            'MERCHANT' => '20904330',
            'TERMINAL' => '20907286',
        ]
    ];
    $osc_sign_key = [
        2 => '0089e7e9a1abca22ae2d621a050d8966'
    ];

    foreach ($osc_merch[$merch_index] as $key => $val) {
        $oschad_merchant_settings[$key] = $val;
    }
    $oschad_sign_key = $osc_sign_key[$merch_index];
}
