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

    if ($error) {
        ?><h2 class="big-error-message"><?= $error; ?></h2><?php
        unset($_SESSION['paybill']);
        return;
    }
?>
<div class="form-subtitle subtitle-bg-green">Оберіть, будь ласка, спосіб сплати:</div>
<form class="form-block full-width" action="<?= BASE_URL; ?>/post/cabinet/object-item/checkout/" method="post">
    <div class="paysystems">
        <?php

            if (in_array('visa', $pay_systems)) {
                ?>
                <div class="check-box-line">
                    <span id="checkbox_percent_visa" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $tasSum; ?>" data-paysystem-key="tas_visa"></span>
                    <label onclick="$('#checkbox_percent_visa').click();">
                        <img alt="visa" src="<?= BASE_URL; ?>/assets/images/paysystems/visa-logo.png" />
                        <span class="text-label">Карта Visa, Visa Electron</span>
                    </label>
                </div>
                <?php
            }

            if (in_array('mastercard', $pay_systems) || in_array('tas', $pay_systems)) {
                ?>
                <div class="check-box-line">
                    <span id="checkbox_percent_mastercard" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $tasSum; ?>" data-paysystem-key="mastercard_box"></span>
                    <label onclick="$('#checkbox_percent_mastercard').click();">
                        <img alt="mastercard" src="<?= BASE_URL; ?>/assets/images/paysystems/mastercard-logo.png" />
                        <span class="text-label">Карта MasterCard, Maestro</span>
                    </label>
                </div>
                <div class="paybill-ps-item paybill-ps-item-mastercard paybill-ps-item-oschadbank paybill-ps-item-mastercard_box" style="display: none;">
                    <div class="paybill-ps-sub-items">
                        <div class="check-box-line">
                            <span id="checkbox_percent_mastercard_1" class="niceCheck radio"><span class="dotted-line"></span><input type="radio" name="percent" data-paysystem-sum="<?= $oschadbankSum; ?>" data-paysystem-key="oschadbank"></span>
                            <label onclick="$('#checkbox_percent_mastercard_1').click();">
                                <img alt="mastercard" src="<?= BASE_URL; ?>/assets/images/paysystems/mastercard-logo.png" />
                                <span class="text-label">Інші банки</span>
                            </label>
                        </div>
                        <div class="check-box-line">
                            <span id="checkbox_percent_mastercard_2" class="niceCheck radio"><span class="dotted-line"></span><input type="radio" name="percent" data-paysystem-sum="<?= $mastercardSum; ?>" data-paysystem-key="mastercard"></span>
                            <label onclick="$('#checkbox_percent_mastercard_2').click();">
                                <img alt="Аваль" src="<?= BASE_URL; ?>/assets/images/paysystems/aval.png" />
                                <span class="text-label">Райффайзен Банк Аваль</span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php
            }

            if ($object['city_id'] == Street::KIEV_ID) {
                ?>
                <div class="check-box-line">
                    <span id="checkbox_percent_oschad" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $tasSum; ?>" data-paysystem-key="oschad_box"></span>
                    <label onclick="$('#checkbox_percent_oschad').click();">
                        <img alt="visa" src="<?= BASE_URL; ?>/assets/images/paysystems/oschadbank.png" />
                        <span class="text-label">Ощадбанк</span>
                    </label>
                </div>

                <div class="paybill-ps-item paybill-ps-item-oschad paybill-ps-item-oschad_mycard paybill-ps-item-oschad_box" style="display: none;">
                    <div class="paybill-ps-sub-items">
                        <div class="check-box-line">
                            <span id="checkbox_percent_oschad_1" class="niceCheck radio checked"><span class="dotted-line"></span><input type="radio" name="percent" data-paysystem-sum="<?= $oschadSum; ?>" checked="checked" data-paysystem-key="oschad"></span>
                            <label onclick="$('#checkbox_percent_oschad_1').click();">
                                <img alt="" style="width: 0; height: 32px;" src="<?= BASE_URL; ?>/assets/images/paysystems/oschadbank.png" />
                                <span style="left: -25px;" class="text-label">Картка Киянина</span>
                            </label>
                        </div>
                        <div class="check-box-line">
                            <span id="checkbox_percent_oschad_2" class="niceCheck radio"><span class="dotted-line"></span><input type="radio" name="percent" data-paysystem-sum="<?= $oschad_mycardSum; ?>" data-paysystem-key="oschad_mycard"></span>
                            <label onclick="$('#checkbox_percent_oschad_2').click();">
                                <img alt="" style="width: 0; height: 32px;" src="<?= BASE_URL; ?>/assets/images/paysystems/oschadbank.png" />
                                <span style="left: -25px;" class="text-label">Моя Картка</span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php
            } elseif ($object['city_id'] == Street::ODESSA_ID) {
                if (in_array('webmoney', $pay_systems)) {
                    ?>
                    <div class="check-box-line">
                        <span id="checkbox_percent_webmoney" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $webmoneySum; ?>" data-paysystem-key="webmoney"></span>
                        <label onclick="$('#checkbox_percent_webmoney').click();">
                            <img alt="webmoney" src="<?= BASE_URL; ?>/assets/images/paysystems/wm-logo.png" />
                            <span class="text-label">WebMoney</span>
                        </label>
                    </div>
                    <?php
                }
            }
        ?>
    </div>

    <div class="input">
        <div class="pay-item">Сплата комунальних послуг (<b>Код: <?= $payment_id; ?></b>)</div>
        <span class="total-sum"><?= $total_sum; ?> грн</span>
    </div>
    <div class="input">
        <div class="pay-item">Комісія</div>
        <span class="total-sum"><span id="comission_sum"></span></span>
    </div>
    <div class="input">
        <div class="pay-item">Разом до сплати:</div>
        <div class="total-sum" id="totalBillSum"><?= $totalBillSum; ?> грн</div>
    </div>

    <div class="input align-center">
        <div class="btn-box">
            <button class="btn green bold">Продовжити</button>
        </div>
        <input type="hidden" name="cctype" id="cctype" value="<?= $pay_systems[0]; ?>">
        <input type="hidden" name="flat_id" value="<?= $flat_id; ?>">
        <input type="hidden" value="1" name="checkout_submited">
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        getShoppingCartTotal('<?= $total_sum; ?>', '<?= $tasSum; ?>', 'tas_visa');
      
        $(".niceCheck").click(function() {
            changeCheck($(this), 'check-group');
        });
        
        $('#checkbox_percent_visa').click();

        $("#checkbox_percent_mastercard").click(function() {
            $('#checkbox_percent_mastercard_1').click();
        });
        $("#checkbox_percent_oschad").click(function() {
            $('#checkbox_percent_oschad_1').click();
        });

        $("input[name=percent]").change(function() {
            var checked_el = $("input[name=percent]:checked");
            var ps_key = $(checked_el).attr('data-paysystem-key');
            var ps_sum = $(checked_el).attr('data-paysystem-sum');
            getShoppingCartTotal('<?= $total_sum; ?>', ps_sum, ps_key);
        });
    });
</script>
