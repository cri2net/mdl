<?php
    try {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $__route_result['values']['id']);
        if (!$payment || !$payment['go_to_payment_time'] || ($payment['user_id'] != $__userData['id'])) {
            throw new Exception(ERROR_GET_PAYMENT);
        }

        $services = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id={$payment['id']}");
        $file = ROOT . '/protected/scripts/cabinet/payments/details/' . $payment['type'] . '.php';

        if (!file_exists($file)) {
            throw new Exception(ERROR_SHOW_PAYMENT);
        }

    } catch (Exception $e) {
        ?>
        <h2 class="big-error-message"><?= $e->getMessage(); ?></h2>
        <?php
        return;
    }

    require_once($file);
