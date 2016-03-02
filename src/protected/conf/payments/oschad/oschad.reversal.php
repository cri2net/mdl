<?php
//require_once('../../../classes/Oschad.php');
if(!isset($_GET['oid'])) die('order id not set');
if(!isset($_GET['am'])) die('amount not set');
if(!isset($_GET['rrn'])) die('rrn not set');
if(!isset($_GET['intref'])) die('IntRef not set');

require_once('oschad.conf.php');
$oschad = new Oschad();
$oschad->set_merchant($oschad_merchant_settings);
$oschad->set_reversal($_GET['oid'], $_GET['am'], $_GET['rrn'], $_GET['intref']);
$oschad->sign($oschad_sign_key);

echo '<p>reversal query: <pre>';
$oschad->get_fields();
echo '</pre></p>';

$res = Http::HttpPost($payment_form_action, $oschad->get_fields(), false);

echo '<p>reversal response: <pre>';
print_r($res);
echo '</pre></p>';
