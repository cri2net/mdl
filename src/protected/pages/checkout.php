<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    try {
        $pay_system = $_POST['cctype'];
        $user_id = Authorization::getLoggedUserId();
        if (!in_array($pay_system, ShoppingCart::getActivePaySystems())){
            throw new Exception("UNKNOW PAY SYSTEM $pay_system");
        }

        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['payment_id']);
        if ($_payment == null) {
            throw new Exception("UNKNOW PAYMENT ID {$_SESSION['payment_id']}");
        }

        $_debp_sum = $_payment['summ_plat'];
        $percent = ShoppingCart::getPercent($_debp_sum);
        $percent = $percent[$pay_system]['percent'];
        $payment_id = $_payment['id'];


        $commissionSum = ShoppingCart::getPercentSum($_debp_sum, $pay_system);
        $file = ROOT . "/protected/conf/payments/$pay_system/$pay_system";
        if (file_exists($file . ".conf.php")) {
            require_once($file . ".conf.php");
        }

        $totalAmount = $_debp_sum + $commissionSum;
        
        $cdata = array(
            'processing' => $pay_system,
            'summ_komis' => $commissionSum,
            'summ_total' => $totalAmount,
            'persent' => $percent,
            'go_to_payment_time' => microtime(true),
        );
        PDO_DB::updateWithWhere($cdata, ShoppingCart::TABLE, "id='{$_payment['id']}' AND user_id='$user_id'");

        $purchaseTime = strftime("%y%m%d%H%M%S");
        
        $sd = session_id();
        $totalAmountKop = $totalAmount * 100;
        
        if (file_exists($file . ".process.php")) {
            require_once($file . ".process.php");
        }
        
        $debt_sum = str_replace(".", ",", $_debp_sum);
        $commissionSum = str_replace(".", ",", $commissionSum);
        $totalAmount = str_replace(".", ",", $totalAmount);
        unset($_SESSION['payment_id']);

        ShoppingCart::send_payment_to_reports($_payment['id']);
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
?>
<h1 class="big-title">Електронна каса</h1>
<?php
    if ($error) {
        ?><h2 class="big-error-message"><?= $error; ?></h2><?php
        return;
    }
?>
<p class="p-ch">Підтвердіть правильність введених даних.</p>
<div class="pays-table2">
    <form target="<?= $payment_form_target; ?>" action="<?= $payment_form_action; ?>" method="post" name="f" id="f">
        <table width="100%" border="0" class="pays-table-summ" style="border-collapse: collapse; font-size: 11pt">
            <tr>
                <td class="frst" nowrap="nowrap">&nbsp;</td>
                <td width="74%">Сплата комунальних послуг (Код: <?= $payment_id; ?>)</td>
                <td width="15%" nowrap="nowrap">&nbsp;</td>
                <td width="15%" nowrap="nowrap" style="text-align: right;"><?= $debt_sum; ?> грн</td>
            </tr>
            <tr>
                <td class="frst" nowrap="nowrap">&nbsp;</td>
                <td width="74%">Послуги порталу </td>
                <td width="15%" nowrap="nowrap">&nbsp;</td>
                <td width="15%" nowrap="nowrap" style="text-align: right;"><?= $commissionSum; ?> грн</td>
            </tr>
            <tr>
                <td class="frst" nowrap="nowrap">&nbsp;</td>
                <td width="74%">Усього: </td>
                <td width="15%" nowrap="nowrap">&nbsp;</td>
                <td width="15%" nowrap="nowrap" style="text-align: right;"><?= $totalAmount; ?> грн</td>
            </tr>
            <tr>
                <td class="frst" nowrap="nowrap">&nbsp;</td>
                <td width="74%" nowrap="nowrap">&nbsp;</td>
                <td width="15%" nowrap="nowrap">&nbsp;</td>
                <td width="15%" nowrap="nowrap">
                    <input type="submit" value="Сплатити">
                </td>
            </tr>
        </table>
        <?php
            if (file_exists($file . ".payform.php")) {
                require_once($file . ".payform.php");
            }
        ?>
    </form>
</div>
<?php
    if ($pay_system == 'imeks') {
        ?>
        <input type="hidden" name="cart_status" id="cart_status" size="2" value="0">
        <input type="hidden" id="O_ID" value="<?= $payment_id; ?>">

        <div id="waiting" class="visible">
        </div>
        <div id="check_status" class="invisible">
        <br>
        <form action="<?= $payment_form_action; ?>" method="post" name="payment" id="payment">
            <input type="hidden" id="OrderID" name="OrderID" value="<?= $payment_id; ?>">
            <input type="hidden" name="AfterPayment" value="1">
            <center><input type="button" class="button_orange" value="   Далі >>   " onclick="imeksPaymentNext();"></center>
        </form>
        </div>
        <?php
    }
?>