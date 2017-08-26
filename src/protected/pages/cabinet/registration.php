<div class="h1-line">
    <h1>Реєстрація</h1>
    <div class="already-have">
        <a href="<?= BASE_URL; ?>/cabinet/login/">У мене вже є аккаунт</a> <br>
        <a href="<?= BASE_URL; ?>/cabinet/restore/">Забули пароль?</a> <br>
    </div>
</div>
<?php
    if (isset($_SESSION['registration']['status'])) {
        ?>
        <h2 class="big-error-message">Під час реєстрації виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['registration']['error']['text']; ?></div>
        <?php
        unset($_SESSION['registration']['status']);
    } elseif (isset($_SESSION['registration']['show_message'])) {
        ?>
        <h2 class="big-<?= $_SESSION['registration']['show_message']['type']; ?>-message"><?= $_SESSION['registration']['show_message']['text']; ?></h2>
        <?php
        // наверно надо каждый раз это показывать
        // unset($_SESSION['registration']['show_message']);
    }

    if (Authorization::isLogin()) {
        ?><h2 class="big-success-message">Ви вже зареєстровані</h2> <?php
        return;
    }

    $_reg_name       = (isset($_SESSION['registration']['name']))       ? $_SESSION['registration']['name'] : '';
    $_reg_fathername = (isset($_SESSION['registration']['fathername'])) ? $_SESSION['registration']['fathername'] : '';
    $_reg_lastname   = (isset($_SESSION['registration']['lastname']))   ? $_SESSION['registration']['lastname'] : '';
    $_reg_email      = (isset($_SESSION['registration']['email']))      ? $_SESSION['registration']['email'] : '';
    $_reg_phone      = (isset($_SESSION['registration']['phone']))      ? $_SESSION['registration']['phone'] : '';

    $_reg_name = htmlspecialchars($_reg_name, ENT_QUOTES);
    $_reg_fathername = htmlspecialchars($_reg_fathername, ENT_QUOTES);
    $_reg_lastname = htmlspecialchars($_reg_lastname, ENT_QUOTES);
    $_reg_email = htmlspecialchars($_reg_email, ENT_QUOTES);
?>
<div class="registration">
    <div class="form-block">
        <form onsubmit="registration_form_submit(); top.postMessage('register-form-send', 'http://cks.kiev.ua');" method="post" action="<?= BASE_URL; ?>/post/cabinet/registration/">
            <input type="text" name="country" value="" style="display:none;">
            <div class="input">
                <label>Прізвище <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="text" name="lastname" id="reg-lastname" value="<?= $_reg_lastname; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
            </div>
            <div class="input">
                <label>Ім’я: <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="text" name="name" id="reg-name" value="<?= $_reg_name; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
            </div>
            <div class="input">
                <label>По-батькові <br>
                    <input class="txt form-txt-input" type="text" name="fathername" id="reg-fathername" value="<?= $_reg_fathername; ?>">
                </label>
            </div>
            <div class="input">
                <label>Електронна пошта <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="email" name="email" id="reg-email" value="<?= $_reg_email; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
            </div>
            <div class="input">
                <label>Телефон <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="setTimeout(function(){registration_ckeck_empty_fileld($('#reg-phone'))}, 200);" required="required" class="txt form-txt-input" placeholder="+380" type="text" name="phone" id="reg-phone" value="<?= $_reg_phone; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
            </div>
            <div class="input pass">
                <label>Пароль <span class="star-required" title="обов'язкове поле">*</span> <span class="hint">(не менше 6 символів)</span><br>
                    <span class="eye" onclick="registration_show_password();"></span>
                    <span id="registration-password-box">
                        <input style="display:block;" onblur="registration_ckeck_empty_fileld_password(this);" required="required" class="txt form-txt-input" id="reg-password" type="password" name="password">
                        <input style="display:none;" onblur="registration_ckeck_empty_fileld_password(this);" class="txt form-txt-input" id="reg-password-replica" type="text" autocomplete="off">
                    </span>
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                <div id="password-strength-container">
                    <div class="gauge"></div>
                    <div class="title"></div>
                </div>
            </div>
            <div class="input" style="margin-bottom: 20px;">
                <span><input type="checkbox" name="agree[1]" required="required" checked /></span>
                <span class="hint">
                    Реєструючись, Ви підтверджуєте свою згоду на обробку персональних даних, а також підтверджуєте те,
                    що ознайомились та погоджуєтесь з <a href="http://cks.kiev.ua/docs/ugoda_korustuvacha.pdf" target="_blank">Угодою&nbsp;користувача</a>
                    та <a href="http://cks.kiev.ua/docs/zgoda_na_obrobku_danih.pdf" target="_blank">Згодою на збір та обробку персональних даних</a>
                </span>
            </div>
            <div class="input" style="margin-bottom: 20px;">
                <span><input type="checkbox" name="agree[2]" required="required" checked /></span>
                <span class="hint">
                Реєструючись у особистому кабінеті та отримуючи інформацію про об’єкт (особовий рахунок) Ви підтверджуєте, що маєте право на отримання такої інформації по даному об’єкту, а також усвідомлюєте свою персональну відповідальність за її незаконне отримання. 
                Звертаємо Вашу увагу, що статтею 182 кримінального кодексу України встановлено відповідальність за незаконне збирання, зберігання та поширення конфіденційної інформації про особу</span>
            </div>
            <div class="input">
                <button class="btn green bold">Зареєструватися</button>
            </div>
        </form>
    </div>
    <?php require_once(PROTECTED_DIR . '/scripts/cabinet/info-block.php'); ?>
</div>

<script type="text/javascript">
$(function($){
    $("#reg-phone").mask("+999(99)999-99-99", {autoclear: false}).val('<?= $_reg_phone; ?>');
});
</script>
