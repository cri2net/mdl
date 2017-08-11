<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>
<div class="container">
    <content>
        <div class="portlet">
            
<!--         <h1 class="big-title">Cплата штрафів за порушення ПДР</h1>
        <br><br>
 -->
        <?php
            try {
                $regions = Gai::getRegions();

                if (is_array($_SESSION['instant-payments-dai']['columns'])) {
                    foreach ($_SESSION['instant-payments-dai']['columns'] as $key => $value) {
                        $$key = $value;
                    }
                }

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
                
            } catch (Exception $e) {
                $_SESSION['instant-payments-dai']['status'] = false;
                $_SESSION['instant-payments-dai']['error']['text'] = $e->getMessage();
                $_SESSION['instant-payments-dai']['step'] = 'region';
            }
            

            if (isset($_SESSION['instant-payments-dai']['status']) && !$_SESSION['instant-payments-dai']['status']) {
                ?>
                <div>Під час виконання запиту виникла помилка:</div>
                <div class="alert alert-danger"><?= $_SESSION['instant-payments-dai']['error']['text']; ?></div>
                <?php
                unset($_SESSION['instant-payments-dai']['status']);
            }
            
            switch ($_SESSION['instant-payments-dai']['step']) {
                case 'frame':
                    $id = $_SESSION['instant-payments-dai']['dai_last_payment_id'];
                    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
                    $payment_id = $_payment['id'];
                
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

                    $_SESSION['instant-payments-dai']['step'] = 'region';
                    break;

                case 'details':
                    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['instant-payments-dai']['dai_last_payment_id']);
                    $_service = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$_payment['id']}'");
                    $_service = $_service[0];
                    $_service['data'] = @json_decode($_service['data']);
                    
                    require_once(PROTECTED_DIR . '/scripts/cabinet/instant-payments/_payment_details_step.php');
                    break;

                case 'region':
                default:
                    ?>
                    <form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/dai/">
                        <h3>Постанова</h3>
                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    Серія постанови <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" maxlength="3" value="<?= htmlspecialchars($postanova_series, ENT_QUOTES); ?>" type="text" name="postanova_series" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Номер постанови <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" maxlength="8" value="<?= htmlspecialchars($postanova_number, ENT_QUOTES); ?>" type="text" name="postanova_number" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Область <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <select class="form-txt" required="required" name="region" onblur="registration_ckeck_empty_fileld(this);">
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
                            <div class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    Сума штрафу, грн <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($protocol_summ, ENT_QUOTES); ?>" type="text" name="protocol_summ" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Дата постанови <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input id="protocol_date" maxlength="10" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($protocol_date, ENT_QUOTES); ?>" type="text" name="protocol_date" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <h3>Платник штрафу</h3>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    Прізвище <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_lastname, ENT_QUOTES); ?>" type="text" name="penalty_user_lastname" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>

                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Ім’я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_name, ENT_QUOTES); ?>" type="text" name="penalty_user_name" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>
                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    По-батькові <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_fathername, ENT_QUOTES); ?>" type="text" name="penalty_user_fathername" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                            <?php
                                $display_email = (isset($__userData['email']) && filter_var($penalty_user_email, FILTER_VALIDATE_EMAIL))
                                    ? 'display: none;'
                                    : 'display: inline-block;';
                            ?>
                            <div class="form-group" style="<?= $display_email; ?>">
                                <label>
                                    Електронна пошта <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_email, ENT_QUOTES); ?>" type="email" name="penalty_user_email" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>
                        
                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group full-width">
                                <label>
                                    Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input style="width: 655px;" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_address, ENT_QUOTES); ?>" type="text" name="penalty_user_address" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <div class="form-group align-center">
                            <button id="submitOrder" class="btn btn-blue btn-md">Далі</button>
                        </div>
                    </form>
                    <style>
                        #submitOrder:disabled { cursor: default; }
                    </style>
                    <?php
                    break;
            }
        ?>
        </div>
    </content>
</div>
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
