<?php

use cri2net\php_pdo_db\PDO_DB;

$payments = PDO_DB::table_list(
    ShoppingCart::TABLE,
    "user_id={$__userData['id']} AND processing IS NOT NULL",
    "go_to_payment_time DESC"
);

require_once(ROOT . '/protected/scripts/cabinet/payments/_list.php');
