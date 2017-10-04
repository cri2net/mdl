<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    if (!isset($_SESSION['paybill']['payment_id'])) {
        throw new Exception(ERROR_OLD_REQUEST);
    }
    
    $user_id = Authorization::getLoggedUserId();
    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['paybill']['payment_id']);
    $pay_system = $_payment['processing'];
    if ($pay_system != 'tas') {
        throw new Exception('ERROR_OLD_REQUEST');
    }

    $payment_id = $_payment['id'];

    $file = ROOT . "/protected/conf/payments/$pay_system/$pay_system";
    if (file_exists($file . ".conf.php")) {
        require_once($file . ".conf.php");
    }

    if (file_exists($file . ".process.php")) {
        require_once($file . ".process.php");
    }
    
    unset($_SESSION['paybill']['payment_id'], $_SESSION['paybill-post-flag']);
} catch (Exception $e) {
    $error = $e->getMessage();
    ?>
    <div class="container">
        <content>
            <div class="text">
                <h2 class="big-error-message"><?= $error; ?></h2>
            </div>
        </content>
    </div>
    <?php
    return;
}
if (file_exists($file . ".payform.php")) {
    require_once($file . ".payform.php");
}
