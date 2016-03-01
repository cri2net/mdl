<?php
require_once('protected/classes/Oschad.php');
require_once('protected/conf/payments/oschad/oschad.conf.php');
$oschad = new Oschad();
$oschad->set_merchant($oschad_merchant_settings);
$oschad->set_reversal('0094199', '2.01', '606199657059', '4425B8D52A574566');

$oschad->sign($oschad_sign_key);

$processing_data = array(
    'first' => $oschad->get_fields(),
    'dates' => array(), // массив с ключами для requests
    'requests' => array() // массив с историей запросов
);
echo '<form action="https://3ds.oschadnybank.com/cgi-bin/cgi_link" method="POST">';
echo $oschad->get_html_fields(true);
echo '</form>';
?>
