<?php
    // проверка, что мы пришли сюда после post запроса на оплату
    if (!isset($_SESSION['paybill-post-flag'])) {
        $error = ERROR_OLD_REQUEST;
        unset($_SESSION['paybill']);
    } else {
        // Данные из сессии по этой странице не обнуляем.
        // Чтоб можно было обновить страницу и увидеть эти же данные
        // Обнулим потом, на странице checkout
    }

    $flat_id = $__route_result['values']['id'];

    $payment_id = $_SESSION['paybill']['payment_id'];
    $total_sum = $_SESSION['paybill']['total_sum'];
    $totalBillSum = $_SESSION['paybill']['totalBillSum'];
    $pay_systems = ShoppingCart::getActivePaySystems();

    foreach ($pay_systems as $tmp) {
        $name = $tmp . 'Sum';
        $$name = ShoppingCart::getPercentSum($total_sum, $tmp);
    }

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
<form class="form-block full-width" action="<?= BASE_URL; ?>/post/cabinet/object-item/checkout/" method="post">
    <div class="paysystems">
        <div class="check-box-line">
            <span id="checkbox_percent_mastercard_3" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $oschadSum; ?>" data-paysystem-key="oschad"></span>
            <label onclick="$('#checkbox_percent_mastercard_3').click();">
                <img alt="" style="height: 32px;" src="<?= BASE_URL; ?>/assets/images/paysystems/oschadbank.png" />
                <span class="text-label">Ощадбанк Картка Киянина</span>
            </label>
        </div>
    </div>

    <div class="input">
        <div class="pay-item" style="float: none;">Сплата комунальних послуг (<b>Код: <?= $payment_id; ?></b>)</div>
        <span class="total-sum"><?= $total_sum; ?> грн</span>
    </div>
    <div class="input">
        <div class="pay-item" style="float: none;">Комісія</div>
        <span class="total-sum"><span id="comission_sum"></span></span>
    </div>

    <div class="input align-center">
        <div class="btn-box">
            <button class="btn green bold">Продовжити</button>
        </div>
        <input type="hidden" name="cctype" id="cctype" value="<?= $pay_systems[0]; ?>">
        <input type="hidden" name="flat_id" value="<?= $flat_id; ?>">
        <input type="hidden" value="1" name="checkout_submited">
    </div>

    <?php
        if (!empty($psp_id)) {
            ?>
            <span style="color: #01b671; font-size: 22px;">
                <br>
                <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>Інші способи</span>
                <br><br>
            </span>
            <iframe style="min-width: 760px; width: 100%; min-height: 600px;" src="https://fc.gerc.ua:8443/api/card.php?site_id=<?= Psp::SITE_ID; ?>&oper_id=<?= $psp_id; ?>" frameborder="0"></iframe>
            <br><br>
            <?php
        }
    ?>
</form>
</div>
</content>
</div>
<script>
    $(document).ready(function(){
      
        $(".niceCheck").click(function() {
            changeCheck($(this), 'check-group');
        });
        
        $("input[name=percent]").change(function() {
            var checked_el = $("input[name=percent]:checked");
            var ps_key = $(checked_el).attr('data-paysystem-key');
            var ps_sum = $(checked_el).attr('data-paysystem-sum');
            getShoppingCartTotal('<?= $total_sum; ?>', ps_sum, ps_key);
        });
    });
</script>
