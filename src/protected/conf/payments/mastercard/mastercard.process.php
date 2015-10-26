<?php
    $str_to_sign = "$MerchantID;$TerminalID;$purchaseTime;{$_payment['id']};$Currency;$totalAmountKop;$sd;";
    $fp = fopen(ROOT . "/protected/conf/payments/$pay_system/$MerchantID.pem", "r");
    $priv_key = fread($fp, 8192);
    fclose($fp);
    $pkeyid = openssl_get_privatekey($priv_key);
    openssl_sign($str_to_sign, $signature, $pkeyid);
    openssl_free_key($pkeyid);
    $signature = base64_encode($signature);
    
    $upc_data = [
        'timestamp' => microtime(true),
        'upc_sd' => $sd,
        'upc_trancode' => '',
        'upc_proxypan' => '',
        'upc_approvalcode' => '',
        'upc_orderid' => $_payment['id'],
        'upc_signature' => $signature,
        'upc_purchasetime' => $purchaseTime,
        'upc_merchantid' => $MerchantID,
        'upc_terminalid' => $TerminalID,
        'upc_xid' => '',
        'upc_totalamount' => $totalAmountKop,
        'upc_currency' => $Currency,
        'upc_rrn' => '',
    ];

    $processing_data = [
        'first' => $upc_data,
        'dates' => [], // массив с ключами для requests
        'requests' => [], // массив с историей запросов
    ];

    $update_data = ['processing_data' => json_encode($processing_data)];
    PDO_DB::update($update_data, ShoppingCart::TABLE, $_payment['id']);
