<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<div class="h1-line-cabinet">
    <h1 class="big-title">Платежі до бюджету</h1>
    <div style="display: inline-block; margin-top: 17px; font-size: 14px;"><a href="<?= BASE_URL; ?>/cabinet/instant-payments/requisites/">довільні реквізити</a></div>
</div>
<?php

$pay_frame_width = 840;

try {

    $states = Budget::getStates();

    $email    = (isset($__userData['email']))    ? $__userData['email'] : '';
    $name     = (isset($__userData['name']))     ? $__userData['name'] : '';
    $lastname = (isset($__userData['lastname'])) ? $__userData['lastname'] : '';
    $inn      = '';
    $address  = '';

    if (is_array($_SESSION['instant-payments-budget']['columns'])) {
        foreach ($_SESSION['instant-payments-budget']['columns'] as $key => $value) {
            $$key = $value;
        }
    }
    
    if (!isset($_SESSION['instant-payments-budget']['step'])) {
        $_SESSION['instant-payments-budget']['step'] = 'region';
    }
} catch (Exception $e) {
    $_SESSION['instant-payments-budget']['status'] = false;
    $_SESSION['instant-payments-budget']['error']['text'] = $e->getMessage();
    $_SESSION['instant-payments-budget']['step'] = 'region';
}


if (isset($_SESSION['instant-payments-budget']['status']) && !$_SESSION['instant-payments-budget']['status']) {
    ?>
    <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
    <div class="error-description"><?= $_SESSION['instant-payments-budget']['error']['text']; ?></div>
    <?php
    unset($_SESSION['instant-payments-budget']['status']);
}

switch ($_SESSION['instant-payments-budget']['step']) {
    case 'frame':
        $id = $_SESSION['instant-payments-budget']['budget_last_payment_id'];
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
        $payment_id = $_payment['id'];
        
        $file = PROTECTED_DIR . "/conf/payments/tas/tas";
        if (file_exists($file . ".conf.php")) {
            require_once($file . ".conf.php");
        }
        if (file_exists($file . ".process.php")) {
            require_once($file . ".process.php");
        }
        if (file_exists($file . ".payform.php")) {
            require_once($file . ".payform.php");
        }

        $_SESSION['instant-payments-budget']['step'] = 'region';
        break;

    case 'details':
        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['instant-payments-budget']['budget_last_payment_id']);
        $_service = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$_payment['id']}'");
        $_service = $_service[0];
        $_service['data'] = @json_decode($_service['data']);

        require_once(PROTECTED_DIR . '/scripts/cabinet/instant-payments/_payment_details_step.php');
        break;

    case 'region':
    default:
        ?>
        <form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/budget/">
            <h3>Дані про платіж</h3>
            <div style="display: inline-block; float: none; width: 100%;">
                <div class="field-group">
                    <label>
                        Область <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <select class="txt txt-full" required="required" id="state" name="state">
                            <option value="0">-- Виберіть область --</option>
                            <?php
                                foreach ($states as $key => $item) {
                                    ?><option value="<?= $item['id']; ?>"><?= $item['name']; ?></option><?php
                                }
                            ?>
                        </select>
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group">
                    <label>
                        Район <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <select class="txt txt-full" required="required" id="area" name="area"></select>
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group">
                    <label>
                        Послуга <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <select class="txt txt-full" required="required" id="service" name="service"></select>
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group">
                    <label>
                        Одержувач <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <select class="txt txt-full" required="required" id="firme" name="firme"></select>
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
            </div>

            <h3>Дані платника</h3>
            <div style="display: inline-block; float: none; width: 100%;">
                <div class="field-group">
                    <label>
                        Прізвище <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($lastname, ENT_QUOTES); ?>" name="lastname" class="txt txt-full" required="required" type="text">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group">
                    <label>
                        Ім'я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($name, ENT_QUOTES); ?>" name="name" class="txt txt-full" required="required" type="text">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group">
                    <label>
                        ІПН <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($inn, ENT_QUOTES); ?>" name="inn" class="txt txt-full" required="required" type="text">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group">
                    <label>
                        Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($address, ENT_QUOTES); ?>" name="address" class="txt txt-full" required="required" type="text">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <?php
                    $display_email = (isset($__userData['email']) && filter_var($email, FILTER_VALIDATE_EMAIL))
                        ? 'display: none;'
                        : '';
                ?>
                <div class="field-group" style="<?= $display_email; ?>">
                    <label>
                        E-Mail <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($email, ENT_QUOTES); ?>" name="email" class="txt txt-full" required="required" type="text">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
            </div>

            <h3 style="margin: 25px 0 0 0;">Сума платежа, грн</h3>
            <div style="display: inline-block; float: none; width: 100%;">
                <div class="field-group">
                    <label>
                        <input onblur="registration_ckeck_empty_fileld(this);" maxlength="8" value="<?= htmlspecialchars($summ, ENT_QUOTES); ?>" name="summ" class="txt" required="required" type="text">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
            </div>  
            <div class="field-group align-center">
                <button id="submitOrder" class="btn green bold">Далі</button>
            </div>
        </form>
        <script type="text/javascript">
            $("#area").depdrop({
                depends: ['state'],
                url: '<?= BASE_URL; ?>/ajax/json/direct_get_area/',
                language: 'ru'
            });
            $("#service").depdrop({
                depends: ['area'],
                url: '<?= BASE_URL; ?>/ajax/json/direct_get_service/',
                language: 'ru'
            });
            $("#firme").depdrop({
                depends: ['area', 'service'],
                url: '<?= BASE_URL; ?>/ajax/json/direct_get_firme/',
                language: 'ru'
            });
        </script>
        <?php
}
