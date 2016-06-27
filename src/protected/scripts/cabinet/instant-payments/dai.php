<h1 class="big-title">Cплата штрафів за порушення ПДР</h1>
<br><br><br>

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
    
    if (($_GET['step'] == 'success') || ($_GET['step'] == 'error')) {

        if (!isset($_SESSION['GAI'])) {
            $this->smarty->assign('error_msg', 'Просмотр оплаты более недоступен');
        } else {
            $record = $Gai->get_transaction($_SESSION['GAI']['id']);

            if ($record['status'] == 'new') {
                // оплата ещё не завершена
            } elseif ($record['status'] == 'success') {
                $_SESSION['instant-payments-dai']['step'] = 'success';
            } else {
                $_SESSION['instant-payments-dai']['status'] = false;
                $_SESSION['instant-payments-dai']['error']['text'] = 'Помилка оплати';
            }
        }
    } elseif (isset($_POST['get_last_step'])) {
        
        $id = $_SESSION['instant-payments-dai']['dai_last_payment_id'];
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
        
        if ($payment) {
            $payment['processing_data'] = json_decode($payment['processing_data']);
            $iframe_src = TasLink::IFRAME_SRC . $payment['processing_data']->first->oid;
            $isFrameStep = true;
        }
   }


   if (isset($_SESSION['instant-payments-dai']['status']) && !$_SESSION['instant-payments-dai']['status']) {
       ?>
       <h2 class="big-error-message">Під час надсилання повідомлення виникла помилка:</h2>
       <div class="error-description"><?= $_SESSION['instant-payments-dai']['error']['text']; ?></div>
       <?php
       unset($_SESSION['instant-payments-dai']['status']);
   }
?>

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
<?php
    
    switch ($_SESSION['instant-payments-dai']['step']) {
        case 'frame':
            ?>
            <div id="tas_frame_box">
                <div id="tas_frame_error" style="display: none; font-size:14px; color: #900; padding: 0 5px 0 40px;">
                    <b>Уважаемые клиенты!</b> <br><br>
                    Для обеспечения максимально безопасного платежа с помощью нашего сервиса просим обновить Ваш браузер до последней доступной версии.
                    (в случае проблем с отображением просим в настройках браузера включить протокол безопасности TLS 1.2)
                </div>
                <iframe id="tas_frame" onload="tas_frame_load();" src="<?= $iframe_src; ?>" frameborder="0" style="width:837px; height:888px;"></iframe>
            </div>
            <script>
                $(document).ready(function(){
                    tas_timeout_id = setTimeout(function(){
                        $('#tas_frame_error').fadeIn(200);
                        $('#tas_frame').css('display', 'none');
                    }, 3500);
                });
                $(document).keydown(function(e) {
                    if ((e.keyCode == 116) || (e.keyCode == 82 && e.ctrlKey)) {
                        return false;
                    }
                });
            </script>
            <?php
            break;

        case 'success':
            ?>
            <form class="gerts-register" name="gerts-register" method="post" action="" id="register">
                <div class="success">
                    <div style="font-size: 18px;">Оплата успешно завершена <br></div>
                    <span><a style="font-size:14px;" onclick="sendInvoice(); return false;" href="#">Скачать квитанцию об оплате в PDF ↓</a></span>
                </div>
            </form>
            <form action="{*$DOMAIN_URL*}" method="post" name="send_invoice" id="send_invoice" style="display:none;">
                <input type="hidden" value="1" name="send_flag">
            </form>
            <?php
            break;

        case 'details':
            ?>
            <div class="oplata-box">
                <div class="title_row">Реквизиты</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span>Получатель</span> <?= $record['dst_name']; ?></p>
                        <p><span>ЕГРПОУ (ОКПО)</span> <?= $record['dst_okpo']; ?></p>
                        <p><span>МФО</span> <?= $record['dst_mfo']; ?></p>
                        <p><span>Расчетный счет</span> <?= $record['dst_rcount']; ?></p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="title_row">Информация о платеже</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">Дата операции</span> <?= $record['date']; ?></p>
                        <p><span style="width:270px;">Получатель платежа</span> <?= $record['dst_name']; ?></p>
                        <p><span style="width:270px;">Назначение платежа</span> <?= $record['dest']; ?></p>
                        <p><span style="width:270px;">Сумма платежа</span> <?= $record['summ_plat']; ?> грн</p>
                    </div>
                    <div class="clr"></div>
                </div>

                <div class="title_row">Информация о плательщике</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">ФИО плательщика</span> <?= $record['vr1']; ?></p>
                        <p><span style="width:270px;">Место проживания (регистрация)</span> <?= $record['vr2']; ?></p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="title_row">Стоимость платежа</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">Сбор за обработку платежа</span> <?= $record['summ_komis']; ?> грн</p>
                        <p><span style="width:270px;">Всего к оплате</span> <?= $record['summ_total']; ?> грн</p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div style="text-align:center;">
                    <form action="" method="post">
                        <input type="hidden" name="get_last_step" value="1">
                        <div class="blue_button registration">
                            <button style="width:240px;" id="submitOrder" class="btn green bold">Перейти к оплате</button>
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
