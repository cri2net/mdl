<?php
$payments = PDO_DB::table_list(
    ShoppingCart::TABLE,
    "user_id={$__userData['id']} AND type IN ('gai', 'kinders') AND processing IS NOT NULL",
    "go_to_payment_time DESC"
);

require_once(ROOT . '/protected/scripts/cabinet/payments/_list.php');
