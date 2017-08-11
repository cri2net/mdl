<?php

use cri2net\php_pdo_db\PDO_DB;

$payments = PDO_DB::table_list(
    ShoppingCart::TABLE,
    "user_id={$__userData['id']} AND processing IS NOT NULL",
    "id DESC"
);

?><div class="portlet" ><?php
require_once(PROTECTED_DIR . '/scripts/cabinet/payments/_list.php');
?></div><?php
