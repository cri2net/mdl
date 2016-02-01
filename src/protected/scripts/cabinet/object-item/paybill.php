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

    $visaSum = ShoppingCart::getPercentSum($total_sum, 'visa');
    $mastercardSum = ShoppingCart::getPercentSum($total_sum, 'mastercard');
    $_test_upcSum = ShoppingCart::getPercentSum($total_sum, '_test_upc');
    $khreshchatykSum = ShoppingCart::getPercentSum($total_sum, 'khreshchatyk');

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
                    <span id="checkbox_percent_visa" class="niceCheck radio checked"><input type="radio" name="percent" checked="checked" data-paysystem-sum="<?= $visaSum; ?>" data-paysystem-key="visa"></span>
                    <label onclick="$('#checkbox_percent_visa').click();">
                        <img alt="visa" src="<?= BASE_URL; ?>/images/paysystems/visa-logo.png" />
                        <span class="text-label">Карта Visa, Visa Electron</span>
                    </label>
                </div>
                <?php
            }
            
            if (in_array('mastercard', $pay_systems)) {
                ?>
                <div class="check-box-line">
                    <span id="checkbox_percent_mastercard" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $mastercardSum; ?>" data-paysystem-key="mastercard"></span>
                    <label onclick="$('#checkbox_percent_mastercard').click();">
                        <img alt="mastercard" src="<?= BASE_URL; ?>/images/paysystems/mastercard-logo.png" />
                        <span class="text-label">Карта MasterCard, Maestro</span>
                    </label>
                </div>
                <?php
            }

            if (in_array('_test_upc', $pay_systems)) {
                ?>
                <div class="check-box-line">
                    <span id="checkbox_percent__test_upc" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $_test_upcSum; ?>" data-paysystem-key="_test_upc"></span>
                    <label onclick="$('#checkbox_percent__test_upc').click();">
                        <img alt="" src="<?= BASE_URL; ?>/images/paysystems/visa-logo.png" />
                        <span style="color:#f00;" class="text-label"><b>Тестовий мерчант UPC</b></span>
                    </label>
                </div>
                <?php
            }

            if (in_array('khreshchatyk', $pay_systems)) {
                ?>
                <div class="check-box-line">
                    <span id="checkbox_percent_khreshchatyk" class="niceCheck radio"><input type="radio" name="percent" data-paysystem-sum="<?= $khreshchatykSum; ?>" data-paysystem-key="khreshchatyk"></span>
                    <label onclick="$('#checkbox_percent_khreshchatyk').click();">
                        <img alt="" class="rounded-img" src="<?= BASE_URL; ?>/images/paysystems/kkk.png" />
                        <span class="text-label">Картка Киянина (КБ Хрещатик)</span>
                    </label>
                </div>
                <div class="paybill-ps-item paybill-ps-item-khreshchatyk" style="display: none;">
                    <div class="paybill-ps-cards">
                        <?php
                            $where = 'user_id=' . Authorization::getLoggedUserId() . " AND type='khreshchatyk' AND is_work=1";
                            $user_cards = PDO_DB::table_list(TABLE_PREFIX . 'user_cards', $where, "pos ASC");

                            if (count($user_cards) == 0) {
                                ?>
                                <div class="paybill-ps-card-item card-error card-error-no-cards">
                                    Немає прив’язаних карток
                                </div>
                                <?php
                            }

                            for ($i = 0; $i < count($user_cards); $i++) {
                                ?>
                                <div class="paybill-ps-card-item">
                                    <span id="khreshchatyk-card-<?= $user_cards[$i]['id']; ?>" class="niceCheck radio <?= ($i == 0) ? 'checked' : ''; ?>"><input value="<?= $user_cards[$i]['id']; ?>" type="radio" name="khreshchatyk-card" <?= ($i == 0) ? 'checked="checked"' : ''; ?>></span>
                                    <label onclick="$('#khreshchatyk-card-<?= $user_cards[$i]['id']; ?>').click();">
                                        <span class="text-label"><?= substr($user_cards[$i]['pan'], 0, 4) . '********' . substr($user_cards[$i]['pan'], 12); ?></span>
                                    </label>
                                </div>
                                <?php
                            }

                            require_once(ROOT . '/protected/scripts/cabinet/add_card_from.php');
                        ?>
                    </div>
                </div>
                <?php
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
        <div class="pay-item">Разом до оплати:</div>
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
        getShoppingCartTotal('<?= $total_sum; ?>', '<?= $visaSum; ?>', '<?= $pay_systems[0]; ?>');
      
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
