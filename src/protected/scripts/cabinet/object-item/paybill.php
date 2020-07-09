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

        $userphone = preg_replace('/[^0-9]/', '', $__userData['mob_phone']);
        $ext_fields = [
            'user_id'  => (int)$__userData['id'],
            'phone_no' => $userphone,
        ];

        $psp_id = Psp2::sendPaymentToGate($payment_id, 'komdebt', $ext_fields);
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

<div class="object">
    <content>
        <div class="object__cabinet">
            <div class="object__inner-cabinet">
                <h1 style="margin-top: 30px; margin-bottom: 35px;">Сплата комунальних послуг #<?= $payment_id; ?> (<?= $total_sum; ?> грн)</h1>
                <?php
                    if (!empty($psp_id)) {
                        ?>
                        <iframe id="psp_iframe" style="width: 100%; height:500px; margin-bottom: 40px;" src="https://fc.gerc.ua:8443/payframe/index.php?common=show&site_id=<?= Psp2::SITE_ID; ?>&oper_id=<?= $psp_id; ?>" frameborder="0"></iframe>
                        <br><br>
                        <?php
                    }
                ?>
            </div>
        </div>
    </content>
</div>
