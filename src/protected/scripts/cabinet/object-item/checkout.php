<?php
    use cri2net\php_pdo_db\PDO_DB;
    
    try {
        if (!isset($_SESSION['paybill']['payment_id'])) {
            throw new Exception(ERROR_OLD_REQUEST);
        }
        
        $user_id = Authorization::getLoggedUserId();
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['paybill']['payment_id']);
        $pay_system = $_payment['processing'];

        $_debp_sum = $_payment['summ_plat'];
        $percent = ShoppingCart::getPercent($_debp_sum);
        $percent = $percent[$pay_system]['percent'];
        $payment_id = $_payment['id'];

        $commissionSum = $_payment['summ_komis'];
        $file = ROOT . "/protected/conf/payments/$pay_system/$pay_system";
        if (file_exists($file . ".conf.php")) {
            require_once($file . ".conf.php");
        }

        $totalAmount = $_debp_sum + $commissionSum;
        $purchaseTime = strftime("%y%m%d%H%M%S");
        
        $sd = session_id();
        $totalAmountKop = $totalAmount * 100;
        
        if (file_exists($file . ".process.php")) {
            require_once($file . ".process.php");
        }
        
        unset($_SESSION['paybill']['payment_id'], $_SESSION['paybill-post-flag']);
    } catch (Exception $e) {
        $error = $e->getMessage();
        ?><h2 class="big-error-message"><?= $error; ?></h2><?php
        return;
    }
?>
<div class="container">
    <content>
        <div class="text">
            <h2 id="wait-please" style="width:270px;" class="big-success-message">Перенаправлення</h2>
        </div>
    </content>
</div>
<form id="paybill-autosubmit-form" target="<?= $payment_form_target; ?>" action="<?= $payment_form_action; ?>" method="post">
    <?php
        if (file_exists($file . ".payform.php")) {
            require_once($file . ".payform.php");
        }
    ?>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $("#paybill-autosubmit-form").submit();
    });
    
    setTimeout(function(){wait_ok_message_timeout('Перенаправлення', $('#wait-please'), 0, 600);}, 600);
</script>
