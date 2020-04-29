<?php
    // проверка, что мы пришли сюда после post запроса на оплату
    if (!isset($_SESSION['paybill-post-flag'])) {
        $error = ERROR_OLD_REQUEST;
        unset($_SESSION['paybill']);
    }

    $flat_id = $__route_result['values']['id'];

    $payment_id = $_SESSION['paybill']['payment_id'];
    $total_sum = $_SESSION['paybill']['total_sum'];

    if (isset($_SESSION['psp_id']['p' . $payment_id])) {
        $psp_id = $_SESSION['psp_id']['p' . $payment_id];
    } else {

        $psp_id = Psp2::sendPaymentToGate($payment_id);
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

<div>Сплата комунальних послуг (<b>Код: <?= $payment_id; ?></b>)</div>
<span><?= $total_sum; ?> грн</span>
<?php
    if (!empty($psp_id)) {
        ?>
        <iframe style="min-width: 760px; width: 100%; min-height: 600px;" src="https://fc.gerc.ua:8443/payframe/index.php?common=show&site_id=<?= Psp2::SITE_ID; ?>&oper_id=<?= $psp_id; ?>" frameborder="0"></iframe>
        <br><br>
        <?php
    }
?>
