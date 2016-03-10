<?php
try {
    $_SESSION['paybill'] = [];
    if (!Authorization::isLogin()) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $original_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_POST['payment_id']);
    if (!$original_payment || ($original_payment['user_id'] != Authorization::getLoggedUserId())) {
        $original_payment = false;
        throw new Exception(ERROR_GET_PAYMENT);
    }

    $flatData = PDO_DB::first(Flat::USER_FLATS_TABLE, "user_id='{$original_payment['user_id']}' AND city_id='{$original_payment['city_id']}' AND flat_id='{$original_payment['flat_id']}'");
    if (!$flatData) {
        throw new Exception(ERROR_NOT_FIND_FLAT_FOR_REPAY);
    }
    
    $total_sum = $original_payment['summ_plat'];
    if ($total_sum == 0) {
        throw new Exception(ERROR_INVALID_ZERO_PAYMENT);
    }

    $percent = ShoppingCart::getPercent($total_sum);
    $pay_systems = ShoppingCart::getActivePaySystems();
    
    for ($i=0; $i < count($pay_systems); $i++) {
        $var = $pay_systems[$i] . 'Sum';
        $$var = str_replace(".", ",", ShoppingCart::getPercentSum($total_sum, $pay_systems[$i]));
    }

    $totalBillSum = $total_sum + ShoppingCart::getPercentSum($total_sum, $pay_systems[0]);
    $totalBillSum = sprintf('%.2f', $totalBillSum);

    $real_servises = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$original_payment['id']}'", "id ASC", $original_payment['count_services']);

    if (count($real_servises) == 0) {
        throw new Exception(ERROR_EMPTY_KOMDEBT_PAYMENT);
    }
    
    $timestamp = microtime(true);

    $payment_data = [
        'user_id' => Authorization::getLoggedUserId(),
        'acq' => '',
        'timestamp' => $timestamp,
        'type' => 'komdebt',
        'flat_id' => $flatData['flat_id'],
        'city_id' => $flatData['city_id'],
        'count_services' => count($real_servises),
        'summ_plat' => $total_sum,
        'summ_komis' => '',
        'summ_total' => '',
        'ip' => USER_REAL_IP,
        'user_agent_string' => HTTP_USER_AGENT
    ];

    $payment_id = PDO_DB::insert($payment_data, ShoppingCart::TABLE);
    foreach ($real_servises as $real_servise) {
        unset($real_servise['id']);
        $real_servise['timestamp'] = $timestamp;
        $real_servise['payment_id'] = $payment_id;
        PDO_DB::insert($real_servise, ShoppingCart::SERVICE_TABLE);
    }
        
    $totalBillSum = str_replace(".", ",", $totalBillSum);
    $total_sum = str_replace(".", ",", $total_sum);

    $_SESSION['paybill']['total_sum'] = $total_sum;
    $_SESSION['paybill']['totalBillSum'] = $totalBillSum;
    $_SESSION['paybill']['payment_id'] = $payment_id;
    $_SESSION['paybill-post-flag'] = 1;

} catch (Exception $e) {
    $_SESSION['payments-repay']['status'] = false;
    $_SESSION['payments-repay']['error']['text'] = $e->getMessage();
}

if ($flatData['id']) {
    return BASE_URL . '/cabinet/objects/'. $flatData['id'] .'/paybill/';
} elseif ($original_payment['id']) {
    return BASE_URL . '/cabinet/payments/details/'. $original_payment['id'] .'/';
}
return BASE_URL . '/cabinet/payments/';
