<?php
    // проверка, что мы пришли сюда после post запроса на оплату
    if (!isset($_SESSION['paybill-post-flag'])) {
        $error = ERROR_OLD_REQUEST;
        unset($_SESSION['paybill']);
    }

    $flat_id = $__route_result['values']['id'];

    $payment_id = $_SESSION['paybill']['payment_id'];
    $total_sum = $_SESSION['paybill']['total_sum'];
    $pay_systems = ShoppingCart::getActivePaySystems();

    if (isset($_SESSION['psp_id']['p' . $payment_id])) {
        $psp_id = $_SESSION['psp_id']['p' . $payment_id];
    } else {

        $psp_id = Psp::sendPaymentToGate($payment_id);
        if (!empty($psp_id)) {
            $_SESSION['psp_id']['p' . $payment_id] = $psp_id;
        }
    }

    if (!empty($error)) {
        ?>
        <div class="container">
            <content>
                <div class="text">
                    <h2 class="big-error-message"><?= $error; ?></h2>
                </div>
            </content>
        </div>
        <?php
        unset($_SESSION['paybill']);
        return;
    }
?>
<div class="container" >
<content>
<div class="portlet" >
<div class="form-subtitle subtitle-bg-green">Оберіть, будь ласка, спосіб сплати:</div>

<div class="input">
    <div class="pay-item" style="float: none;">Сплата комунальних послуг (<b>Код: <?= $payment_id; ?></b>)</div>
    <span class="total-sum"><?= $total_sum; ?> грн</span>
</div>
<?php
    if (!empty($psp_id)) {
        ?>
        <iframe style="min-width: 760px; width: 100%; min-height: 600px;" src="https://fc.gerc.ua:8443/api/card.php?site_id=<?= Psp::SITE_ID; ?>&oper_id=<?= $psp_id; ?>" frameborder="0"></iframe>
        <br><br>
        <?php
    }
?>
</div>
</content>
</div>
