<h1 class="big-title">Cплата штрафів за порушення ПДР</h1>
<br><br>

<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    $regions = Gai::getRegions();

    if (is_array($_SESSION['instant-payments-dai']['columns'])) {
        foreach ($_SESSION['instant-payments-dai']['columns'] as $key => $value) {
            $$key = $value;
        }
    }

    $Gai = new Gai();
    $penalty_user_email      = (isset($__userData['email']))      ? $__userData['email'] : '';
    $penalty_user_name       = (isset($__userData['name']))       ? $__userData['name'] : '';
    $penalty_user_fathername = (isset($__userData['fathername'])) ? $__userData['fathername'] : '';
    $penalty_user_lastname   = (isset($__userData['lastname']))   ? $__userData['lastname'] : '';
    
    $penalty_user_email      = (isset($_SESSION['instant-payments-dai']['columns']['penalty_user_email']))      ? $_SESSION['instant-payments-dai']['columns']['penalty_user_email']      : $penalty_user_email;
    $penalty_user_name       = (isset($_SESSION['instant-payments-dai']['columns']['penalty_user_name']))       ? $_SESSION['instant-payments-dai']['columns']['penalty_user_name']       : $penalty_user_name;
    $penalty_user_fathername = (isset($_SESSION['instant-payments-dai']['columns']['penalty_user_fathername'])) ? $_SESSION['instant-payments-dai']['columns']['penalty_user_fathername'] : $penalty_user_fathername;
    $penalty_user_lastname   = (isset($_SESSION['instant-payments-dai']['columns']['penalty_user_lastname']))   ? $_SESSION['instant-payments-dai']['columns']['penalty_user_lastname']   : $penalty_user_lastname;

    
    if (!isset($_SESSION['instant-payments-dai']['step'])) {
        $_SESSION['instant-payments-dai']['step'] = 'region';
    }

    try {
        
        if (($_GET['step'] == 'success') || ($_GET['step'] == 'error')) {

            if (!isset($_SESSION['instant-payments-dai']['record_id'])) {
                throw new Exception(ERROR_OLD_REQUEST);
            }

            $record = $Gai->get_transaction($_SESSION['instant-payments-dai']['record_id']);

            if ($record['status'] == 'new') {
                // оплата ещё не завершена
            } elseif ($record['status'] == 'success') {
                $_SESSION['instant-payments-dai']['step'] = 'success';
            } else {
                $_SESSION['instant-payments-dai']['status'] = false;
                $_SESSION['instant-payments-dai']['error']['text'] = 'Помилка оплати';
            }

        } elseif (isset($_POST['get_last_step'])) {
            
            $id = $_SESSION['instant-payments-dai']['dai_last_payment_id'];
            $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
            $payment_id = $_payment['id'];
            
            if ($_payment) {
                $_SESSION['instant-payments-dai']['step'] = 'frame';
            } else {
                throw new Exception(ERROR_OLD_REQUEST);
            }
       }

    } catch (Exception $e) {
        $_SESSION['instant-payments-dai']['status'] = false;
        $_SESSION['instant-payments-dai']['error']['text'] = $e->getMessage();
        $_SESSION['instant-payments-dai']['step'] = 'region';
    }
    

    if (isset($_SESSION['instant-payments-dai']['status']) && !$_SESSION['instant-payments-dai']['status']) {
        ?>
        <h2 class="big-error-message">Під час надсилання повідомлення виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['instant-payments-dai']['error']['text']; ?></div>
        <?php
        unset($_SESSION['instant-payments-dai']['status']);
    }
    
    switch ($_SESSION['instant-payments-dai']['step']) {
        case 'frame':
            $file = ROOT . "/protected/conf/payments/tas/tas";
            if (file_exists($file . ".conf.php")) {
                require_once($file . ".conf.php");
            }
            if (file_exists($file . ".process.php")) {
                require_once($file . ".process.php");
            }
            if (file_exists($file . ".payform.php")) {
                require_once($file . ".payform.php");
            }
            break;

        case 'success':
            ?>
            <h2 class="big-success-message">Оплата успішно здійснена</h2>
            <?php
            break;

        case 'details':
            $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['instant-payments-dai']['dai_last_payment_id']);
            $_service = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$_payment['id']}'");
            $_service = $_service[0];
            $_service['data'] = @json_decode($_service['data']);
            ?>
            <div class="oplata-box">
                <div class="title_row">Реквізити</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span>Отримувач</span> <?= $_service['data']->dst_name; ?></p>
                        <p><span>ЕГРПОУ (ОКПО)</span> <?= $_service['data']->dst_okpo; ?></p>
                        <p><span>МФО</span> <?= $_service['data']->dst_mfo; ?></p>
                        <p><span>Розрахунковий рахунок</span> <?= $_service['data']->dst_rcount; ?></p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="title_row">Інформація про платіж</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">Дата операції</span> <?= date('Y.m.d', $_service['timestamp']); ?></p>
                        <p><span style="width:270px;">Одержувач платежу</span> <?= $_service['data']->dst_name; ?></p>
                        <p><span style="width:270px;">Призначення платежу</span> <?= $_service['data']->dest; ?></p>
                        <p><span style="width:270px;">Сума платежа</span> <?= $_payment['summ_plat']; ?> грн</p>
                    </div>
                    <div class="clr"></div>
                </div>

                <div class="title_row">Інформація про платника</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">ФИО плательщика</span> <?= $_service['data']->r1; ?></p>
                        <p><span style="width:270px;">Место проживания (регистрация)</span> <?= $_service['data']->vr2; ?></p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="title_row">Вартість платежу</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">Збір за обробку платежу</span> <?= $_payment['summ_komis']; ?> грн</p>
                        <p><span style="width:270px;">Усього до сплати</span> <?= $_payment['summ_total']; ?> грн</p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div style="text-align:center;">
                    <form action="" method="post">
                        <input type="hidden" name="get_last_step" value="1">
                        <div class="blue_button registration">
                            <button style="width:240px;" id="submitOrder" class="btn green bold">Перейти до сплати</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php
            break;

        case 'region':
        default:
            ?>
            <form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/dai/">
                <h3>Постанова</h3>
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            Серія постанови <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" maxlength="3" value="<?= htmlspecialchars($postanova_series, ENT_QUOTES); ?>" type="text" name="postanova_series" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Номер постанови <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" maxlength="8" value="<?= htmlspecialchars($postanova_number, ENT_QUOTES); ?>" type="text" name="postanova_number" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Область <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <select class="txt" required="required" name="region" onblur="registration_ckeck_empty_fileld(this);">
                                <option value="">-- Будь ласка, оберіть --</option>
                                <?php
                                    foreach ($regions as $item) {
                                        ?><option <?= ($item['ID_AREA'] == $region) ? 'selected="selected"' : ''; ?> value="<?= $item['ID_AREA']; ?>"><?= $item['NAME_STATE']; ?></option> <?php
                                    }
                                ?>
                            </select>
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            Сума штрафу, грн <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" maxlength="8" value="<?= htmlspecialchars($protocol_summ, ENT_QUOTES); ?>" type="text" name="protocol_summ" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Дата постанови <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input id="protocol_date" maxlength="10" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($protocol_date, ENT_QUOTES); ?>" type="text" name="protocol_date" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <h3>Платник штрафу</h3>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            Прізвище <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" maxlength="3" value="<?= htmlspecialchars($penalty_user_lastname, ENT_QUOTES); ?>" type="text" name="penalty_user_lastname" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>

                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Ім’я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" maxlength="3" value="<?= htmlspecialchars($penalty_user_name, ENT_QUOTES); ?>" type="text" name="penalty_user_name" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            По-батькові <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" maxlength="8" value="<?= htmlspecialchars($penalty_user_fathername, ENT_QUOTES); ?>" type="text" name="penalty_user_fathername" class="txt" required="required">
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

                <div class="field-group align-center">
                    <button id="submitOrder" class="btn green bold">Далі</button>
                </div>
            </form>
            <style>
                #submitOrder:disabled { cursor:default; }
            </style>
            <?php
            break;
    }
?>
<style type="text/css">
    .details_row { padding-top:10px; padding-left:15px; }
    .details_row .right_details { float:left; }
    .clr { clear: both; }
    .title_row {background-color: #ededed; border-bottom: 1px solid #d6d6d6; border-top: 1px solid #d6d6d6; color: #21234b; font-size: 16px; height: 38px; line-height: 38px; padding-left: 17px; }
    .details_row .right_details p { line-height: 22px; padding-bottom: 10px; font-size: 14px; }
    .details_row .right_details p span {color: #707780; float: left; font-weight: bold; width: 130px; }
</style>
<script type="text/javascript">
    $(document).ready(function(){
        $("#protocol_date").datepicker({
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat:"dd.mm.yy",
            maxDate: '0'
        });
    });
</script>
