<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<h1 class="big-title">Сплата послуг ЦКС</h1>
<?php
    try {
        $plat_list = CKS::getPlatList();
        $districts = CKS::getDistricts();

        if (is_array($_SESSION['instant-payments-cks']['columns'])) {
            foreach ($_SESSION['instant-payments-cks']['columns'] as $key => $value) {
                $$key = $value;
            }
        }

        $penalty_user_email      = (isset($__userData['email']))      ? $__userData['email'] : '';
        $penalty_user_name       = (isset($__userData['name']))       ? $__userData['name'] : '';
        $penalty_user_fathername = (isset($__userData['fathername'])) ? $__userData['fathername'] : '';
        $penalty_user_lastname   = (isset($__userData['lastname']))   ? $__userData['lastname'] : '';
        
        $penalty_user_email      = (isset($_SESSION['instant-payments-cks']['columns']['penalty_user_email']))      ? $_SESSION['instant-payments-cks']['columns']['penalty_user_email']      : $penalty_user_email;
        $penalty_user_name       = (isset($_SESSION['instant-payments-cks']['columns']['penalty_user_name']))       ? $_SESSION['instant-payments-cks']['columns']['penalty_user_name']       : $penalty_user_name;
        $penalty_user_fathername = (isset($_SESSION['instant-payments-cks']['columns']['penalty_user_fathername'])) ? $_SESSION['instant-payments-cks']['columns']['penalty_user_fathername'] : $penalty_user_fathername;
        $penalty_user_lastname   = (isset($_SESSION['instant-payments-cks']['columns']['penalty_user_lastname']))   ? $_SESSION['instant-payments-cks']['columns']['penalty_user_lastname']   : $penalty_user_lastname;


        if (!isset($_SESSION['instant-payments-cks']['step'])) {
            $_SESSION['instant-payments-cks']['step'] = 'region';
        }
    } catch (Exception $e) {
        $_SESSION['instant-payments-cks']['status'] = false;
        $_SESSION['instant-payments-cks']['error']['text'] = $e->getMessage();
        $_SESSION['instant-payments-cks']['step'] = 'region';
    }

    if (isset($_SESSION['instant-payments-cks']['status']) && !$_SESSION['instant-payments-cks']['status']) {
        ?>
        <br>
        <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['instant-payments-cks']['error']['text']; ?></div>
        <?php
        unset($_SESSION['instant-payments-cks']['status']);
    }

    if ($_SESSION['instant-payments-cks']['step'] == 'frame') {
        $id = $_SESSION['instant-payments-cks']['cks_last_payment_id'];
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

        $_SESSION['instant-payments-cks']['step'] = 'region';

        return;
    }
?>
<form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/cks/">
    <div style="display: inline-block; float: none; width: 100%;">
        <div class="field-group">
            <label>
                Оберіть район м. Київ <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <select class="txt" required="required" id="district">
                    <option value="">-- виберіть --</option>
                    <?php
                        $i = 0;
                        foreach ($districts as $item) {
                            ?>
                            <option value="<?= ++$i; ?>"><?= htmlspecialchars($item['name']); ?></option>
                            <?php
                        }
                    ?>
                </select>
            </label>
        </div>
        <div class="field-group">
            <label>
                Оберіть підрозділ <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <select class="txt" required="required" id="firme" name="firme">
                    <option id="firme_empty" value="">-- виберіть --</option>
                    <?php
                        $i = 0;
                        
                        foreach ($districts as $item) {
                            
                            $i++;

                            foreach ($item['firme_list'] as $firme_item) {
                                ?>
                                <option data-number="<?= $i; ?>" value="<?= $firme_item['id']; ?>"><?= htmlspecialchars($firme_item['name']); ?></option>
                                <?php
                            }
                        }
                    ?>
                </select>
            </label>
        </div>
    </div>  

    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first align-center counters-th" colspan="4">
                    Оберіть послуги до сплати
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bank-name title">
                <td class="first">
                    <div class="check-box-line">
                        <span id="check_all_services" class="niceCheck check-group-rule"><input onchange="checkAllServices($('#check_all_services-elem'));" id="check_all_services-elem" type="checkbox"></span>
                        <label onclick="$('#check_all_services').click();">
                            Послуга
                        </label>
                    </div>
                </td>
                <td style="white-space:nowrap;">До сплати, грн</td>
            </tr>

            <?php
                $row_counter = 0;

                foreach ($plat_list as $item) {
                    $row_counter++;
                    ?>
                    <tr class="item-row <?= ($row_counter % 2 == 0) ? 'even' : 'odd'; ?>">
                        <td class="first">
                            <div class="check-box-line">
                                <span class="niceCheck check-group" id="bill_item_<?= $item['id']; ?>">
                                    <input onclick="$('#bill_item_<?= $item['id']; ?>').click();" onchange="selectService('bill_checkbox_<?= $item['id']; ?>', 'inp_<?= $item['id']; ?>');" id="bill_checkbox_<?= $item['id']; ?>" value="inp_<?= $item['id']; ?>" name="items[]" type="checkbox">
                                </span>
                                <label onclick="$('#bill_item_<?= $item['id']; ?>').click();">
                                    <span><?= htmlspecialchars($item['name']); ?></span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <input disabled="disabled" readonly="readonly" class="bill-summ-input txt num-short green bold form-txt-input" name="inp_<?= $item['id']; ?>_sum" size="20" value="<?= str_replace('.', ',', sprintf('%.2f', $item['sum'])); ?>" onblur="bill_input_blur(this);" onfocus="bill_input_focus(this);" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $item['id']; ?>" type="text">
                        </td>
                    </tr>
                    <?php
                }
            ?>
            <tr class="total-summ-tr">
                <td class="first align-right">Усього, грн:</td>
                <td class="total-sum" id="total_debt">0,00</td>
            </tr>








            <h3>Платник</h3>

            <div style="display: inline-block; float: none; width: 100%;">
                <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                    <label>
                        Прізвище <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_lastname, ENT_QUOTES); ?>" type="text" name="penalty_user_lastname" class="txt" required="required">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>

                <div class="field-group" style="display: inline-block;">
                    <label>
                        Ім’я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_name, ENT_QUOTES); ?>" type="text" name="penalty_user_name" class="txt" required="required">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
            </div>
            <div style="display: inline-block; float: none; width: 100%;">
                <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                    <label>
                        По-батькові <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_fathername, ENT_QUOTES); ?>" type="text" name="penalty_user_fathername" class="txt" required="required">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
                <div class="field-group" style="display: inline-block;">
                    <label>
                        Електронна пошта <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_email, ENT_QUOTES); ?>" type="email" name="penalty_user_email" class="txt" required="required">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
            </div>
            
            <div style="display: inline-block; float: none; width: 100%;">
                <div class="field-group full-width">
                    <label>
                        Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                        <input style="width: 655px;" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_address, ENT_QUOTES); ?>" type="text" name="penalty_user_address" class="txt" required="required">
                    </label>
                    <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                </div>
            </div>




            <tr>
                <td class="align-center" colspan="4">
                    <button disabled="disabled" class="btn green bold big" id="pay_button">Сплатити</button>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<script>
    $(document).ready(function(){

        $("#district").change(function() {
            var val = $("#district").val();
            $('#firme').val('');
            $("#firme option").css('display', 'none');
            $("#firme_empty").css('display', '');

            $('#firme option').each(function(i) {
                var number = $(this).data('number');
                if (number == val) {
                    $(this).css('display', '');
                }
            });
        });

        $(".niceCheck").click(function() {
            changeCheck($(this), 'check-group');
        });

        $("#district").trigger('change');
    });
</script>
