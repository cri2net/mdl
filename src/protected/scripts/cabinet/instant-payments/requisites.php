<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<div class="h1-line-cabinet">
    <h1 class="big-title">Платежі за реквізитами</h1>
    <div style="display: inline-block; margin-top: 17px; font-size: 14px;"><a href="<?= BASE_URL; ?>/cabinet/instant-payments/budget/">скористатися довідником</a></div>
</div>
<?php
    try {
        $__DICT = [
            'summ'       => 'Сума',
            'comiss'     => 'Комісія',
            'total'      => 'Усього',
            'phone'      => 'Номер мобільного телефону',
            'agree_text' => 'Натискаючи кнопку «Продовжити», <br> Ви автоматично погоджуєтесь з',
            'agree_link' => 'умовами',
            'agree_href' => 'https://www.gioc.kiev.ua/help/offers/',
            'btn_next'   => 'Продовжити',
            'step_title' => 'Крок',
            'step1_desc' => 'Дані платника',
            'step2_desc' => 'Дані картки',
            'step3_desc' => 'Підтвердженная платежу',
            'tmp_error'  => 'Сервіс тимчасово недоступний',
            'pay_info'   => 'Дані про платіж',
            'err_empty'  => 'не може бути порожнім',
            'err_email'  => 'E-mail некоректний',
            'err_phone'  => 'Телефон некоректний',
            'err_sum'    => 'Сума платежe не вказана',
        ];

        $keys = [
            'email'     => 'E-mail',
            'firstname' => 'Ім’я',
            'inn'       => 'ІПН',
            'lastname'  => 'Прізвище',
            'address'   => 'Адреса',
            'phone'     => 'Телефон',
            'firme'     => 'Отримувач',
            'account'   => 'Розрахунковий рахунок',
            'bank'      => 'Банк отримувача',
            'dest'      => 'Призначення платежу',
            'mfo'       => 'МФО',
            'okpo'      => 'ЄДРПОУ',
        ];

        $email     = (isset($__userData['email']))     ? $__userData['email'] : '';
        $firstname = (isset($__userData['name']))      ? $__userData['name'] : '';
        $lastname  = (isset($__userData['lastname']))  ? $__userData['lastname'] : '';
        $phone     = (isset($__userData['mob_phone'])) ? $__userData['mob_phone'] : '+38 (0';
        $account = '';
        $address = '';
        $bank = '';
        $dest = '';
        $firme = '';
        $inn = '';
        $mfo = '';
        $okpo = '';

        if (is_array($_SESSION['instant-payments-requisites']['columns'])) {
            foreach ($_SESSION['instant-payments-requisites']['columns'] as $key => $value) {
                $$key = $value;
            }
        }
        
        if (!isset($_SESSION['instant-payments-requisites']['step'])) {
            $_SESSION['instant-payments-requisites']['step'] = 'region';
        }
        
    } catch (Exception $e) {
        $_SESSION['instant-payments-requisites']['status'] = false;
        $_SESSION['instant-payments-requisites']['error']['text'] = $e->getMessage();
        $_SESSION['instant-payments-requisites']['step'] = 'region';
    }
    

    if (isset($_SESSION['instant-payments-requisites']['status']) && !$_SESSION['instant-payments-requisites']['status']) {
        ?>
        <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['instant-payments-requisites']['error']['text']; ?></div>
        <?php
        unset($_SESSION['instant-payments-requisites']['status']);
    }
    
    switch ($_SESSION['instant-payments-requisites']['step']) {
        case 'frame':
            $id = $_SESSION['instant-payments-requisites']['requisites_last_payment_id'];
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

            $_SESSION['instant-payments-requisites']['step'] = 'region';
            break;

        case 'details':
            $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['instant-payments-requisites']['requisites_last_payment_id']);
            $_service = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$_payment['id']}'");
            $_service = $_service[0];
            $_service['data'] = @json_decode($_service['data']);
            
            require_once(PROTECTED_DIR . '/scripts/cabinet/instant-payments/_payment_details_step.php');
            break;

        case 'region':
        default:
            ?>
            <form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/requisites/">

                <h3><?= $__DICT['pay_info']; ?></h3>


                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            <?= $keys['firme']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($firme, ENT_QUOTES); ?>" type="text" name="firme" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            <?= $keys['mfo']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($mfo, ENT_QUOTES); ?>" type="text" name="mfo" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            <?= $keys['bank']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($bank, ENT_QUOTES); ?>" type="text" name="bank" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            <?= $keys['account']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($account, ENT_QUOTES); ?>" type="text" name="account" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; margin-right: 61px;">
                        <label>
                            <?= $keys['okpo']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($okpo, ENT_QUOTES); ?>" type="text" name="okpo" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; margin-right: 61px;">
                        <label>
                            <?= $keys['dest']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input style="width: 655px;" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($dest, ENT_QUOTES); ?>" type="text" name="dest" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>


                <h3><?= $__DICT['step1_desc']; ?></h3>


                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            <?= $keys['lastname']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($lastname, ENT_QUOTES); ?>" type="text" name="lastname" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>

                    <div class="field-group" style="display: inline-block;">
                        <label>
                            <?= $keys['firstname']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($firstname, ENT_QUOTES); ?>" type="text" name="firstname" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            <?= $keys['inn']; ?> <br>
                            <input value="<?= htmlspecialchars($inn, ENT_QUOTES); ?>" type="text" name="inn" class="txt">
                        </label>
                    </div>
                    <?php
                        $display_email = (isset($__userData['email']) && filter_var($email, FILTER_VALIDATE_EMAIL))
                            ? 'display: none;'
                            : 'display: inline-block;';
                    ?>
                    <div class="field-group" style="<?= $display_email; ?>">
                        <label>
                            Електронна пошта <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($email, ENT_QUOTES); ?>" type="email" name="email" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>
                
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group full-width">
                        <label>
                            Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input style="width: 655px;" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($address, ENT_QUOTES); ?>" type="text" name="address" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            <?= $keys['phone']; ?> <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input id="p2p-phone" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($phone, ENT_QUOTES); ?>" type="text" name="phone" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            <?= $__DICT['summ']; ?>, грн <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($summ, ENT_QUOTES); ?>" type="text" name="summ" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div class="field-group align-center">
                    <button id="submitOrder" class="btn green bold">Далі</button>
                </div>
            </form>
            <style>
                #submitOrder:disabled { cursor: default; }
            </style>
            <script type="text/javascript">
                $(function($){
                    $("#p2p-phone").mask("+38 (099) 999 99 99", {placeholder:"+38 (0XX) XXX XX XX", autoclear: false});
                });
            </script>
            <?php
            break;
    }
?>
