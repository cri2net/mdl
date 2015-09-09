<?php
    try {
        $_SESSION['paybill'] = array();
        if (!Authorization::isLogin()) {
            throw new Exception(ERROR_USER_NOT_LOGGED_IN);
        }

        $flatData = Flat::getUserFlatById($_POST['flat_id']);
        if (!$flatData) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }
        
        $total_sum = ShoppingCart::getTotalDebtSum($_POST);
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

        $_POST['flat_id'] = $flatData['flat_id'];
        $_POST['city_id'] = $flatData['city_id'];
        $payment_id = ShoppingCart::add($_POST, Authorization::getLoggedUserId());
        
        $totalBillSum = str_replace(".", ",", $totalBillSum);
        $total_sum = str_replace(".", ",", $total_sum);

        $_SESSION['paybill']['total_sum'] = $total_sum;
        $_SESSION['paybill']['totalBillSum'] = $totalBillSum;
        $_SESSION['paybill']['payment_id'] = $payment_id;
        $_SESSION['paybill-post-flag'] = 1;

    } catch (Exception $e) {
        $_SESSION['object-item']['status'] = false;
        $_SESSION['object-item']['error']['text'] = $e->getMessage();
    }

    if ($flatData['id']) {
        return BASE_URL . '/cabinet/objects/'. $flatData['id'] .'/paybill/';
    }
    return BASE_URL . '/cabinet/objects/';
