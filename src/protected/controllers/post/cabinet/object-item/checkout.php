<?php
try {
    if (!Authorization::isLogin()) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $pay_system = $_POST['cctype'];
    $user_id = Authorization::getLoggedUserId();
    if (!in_array($pay_system, ShoppingCart::getActivePaySystems())){
        throw new Exception("Невідома платіжна система $pay_system");
    }

    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['paybill']['payment_id']);
    if ($_payment == null) {
        throw new Exception("Невідома транзакція {$_SESSION['paybill']['payment_id']}");
    }

    $flatData = Flat::getUserFlatById($_POST['flat_id']);
    if (!$flatData) {
        throw new Exception(ERROR_NOT_FIND_FLAT);
    }
    
    $_debp_sum = $_payment['summ_plat'];
    $percent = ShoppingCart::getPercent($_debp_sum);
    $percent = $percent[$pay_system]['percent'];
    $commissionSum = ShoppingCart::getPercentSum($_debp_sum, $pay_system);
    $totalAmount = $_debp_sum + $commissionSum;
    
    $cdata = [
        'processing'         => $pay_system,
        'summ_komis'         => $commissionSum,
        'summ_total'         => $totalAmount,
        'percent'            => $percent,
        'go_to_payment_time' => microtime(true),
    ];
   
    PDO_DB::updateWithWhere($cdata, ShoppingCart::TABLE, "id='{$_payment['id']}' AND user_id='$user_id'");
    ShoppingCart::send_payment_to_reports($_payment['id']);


    if ($pay_system == 'khreshchatyk') {
        $user_card_id = $_POST['khreshchatyk-card'];

        // проверяем, выбрана ли карта
        if (!$user_card_id) {
            throw new Exception(ERROR_CARD_NO_SELECT_CARD);
        }

        // проверяем, принадлежит ли карта пользователю
        $card = User::getUserCard($user_card_id);
        if (!$card) {
            throw new Exception(ERROR_GET_CARD);
        }

        // проверяем, активна ли карта, обновляем информацию о ней
        if (microtime(true) - $card['updated_at'] > 86400) {
            User::updateUserCardData($card['id']);
        }

        require_once(ROOT . '/protected/conf/payments/khreshchatyk/khreshchatyk.process.php');
        return BASE_URL . "/payment-status/{$_payment['id']}/";
    }

} catch (Exception $e) {
    $_SESSION['object-item']['status'] = false;
    $_SESSION['object-item']['error']['text'] = $e->getMessage();

    if ($flatData['id']) {
        return BASE_URL . '/cabinet/objects/'. $flatData['id'] .'/paybill/';
    }
    return BASE_URL . '/cabinet/objects/';
}

if ($flatData['id']) {
    return BASE_URL . '/cabinet/objects/'. $flatData['id'] .'/checkout/';
}
return BASE_URL . '/cabinet/objects/';
