<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $__route_result['values']['id']);
    if (!$payment || ($payment['user_id'] != $__userData['id'])) {
        throw new Exception(ERROR_GET_PAYMENT);
    }

    $services = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$payment['id']}'");
    $file = PROTECTED_DIR . '/scripts/cabinet/payments/details/' . $payment['type'] . '.php';

    if (!file_exists($file)) {
        throw new Exception(ERROR_SHOW_PAYMENT);
    }

} catch (Exception $e) {
    ?>
    <h2 class="big-error-message"><?= $e->getMessage(); ?></h2>
    <?php
    return;
}

if (isset($_SESSION['payments-repay']['status']) && !$_SESSION['payments-repay']['status']) {
    ?>
    <div class="error-description"><?= $_SESSION['payments-repay']['error']['text']; ?></div>
    <?php
    unset($_SESSION['payments-repay']);
}

?><div class="portlet" ><?php
require_once($file);
?></div><?php
