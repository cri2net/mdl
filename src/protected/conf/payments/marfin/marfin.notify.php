<?php

use cri2net\php_pdo_db\PDO_DB;

/*
параметры 
ORDERID
SUMMA
SIGN
TRANS_DATE
TRANS_TIME
ERRNAME
*/


$mess .= "$date\r\n_POST: ".var_export(@$_POST, true)."\r\n\r\n";

$dir = PROTECTED_DIR . "/logs/paysystems/$paysystem/";
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}
$file = $dir . 'log_notify.txt';
error_log($mess, 3, $file);


$data = $_POST['ORDERID'].";".$_POST['SUMMA'];
$error_code = 0;
$to_update = [];


/* проверки */
/*
0) проверить подпись
1) есть ли в корзине транзакция с номером  ORDERID
2) соответствует ли сумма SUMMA номеру ORDERID
3) разница между временем создания корзины и TRANS_DATE TRANS_TIME  превышает 1 сутки
*/


try {
    if (strcasecmp($_POST['SIGN'], sha1($data)) === 0) {

        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_POST['ORDERID']);

        if (!$payment || ($payment['status'] != 'new') || ($payment['processing'] != 'marfin')) {
            $error_code = 1;
        } elseif ($payment['summ_total'] != $_POST['SUMMA']) {
            $error_code = 2;
        } elseif ($payment['go_to_payment_time'] + 86400 < time()) {
            $error_code = 3;
        }
    } else {
        $error_code = 4;
    }
} catch (Exception $e) {
    $error_code = 5;
}

if ($error_code) {
    echo "ORDERID=&nbsp;".$_POST['ORDERID']."&nbsp;ERROR=&nbsp;".$error_code;
} else {
    echo "ORDERID=&nbsp;".$_POST['ORDERID']."&nbsp;OK";
    $arr['status'] = 'success';
}

PDO_DB::update($to_update, ShoppingCart::TABLE, $payment['id']);
