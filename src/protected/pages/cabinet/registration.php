<div class="h1-line">
    <h1>Реєстрація</h1>
    <div class="already-have">
        <a href="<?= BASE_URL; ?>/cabinet/login/">У мене вже є аккаунт</a>
        <a href="<?= BASE_URL; ?>/cabinet/restore/">Забули пароль?</a>
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
        <form onsubmit="registration_form_submit();" method="post" action="<?= BASE_URL; ?>/post/cabinet/registration/">
            <input type="text" name="country" value="" style="display:none;">
            <div class="input">
                <label>Прiзвище <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="text" name="lastname" id="reg-lastname" value="<?= $_reg_lastname; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
            </div>
            <div class="input">
                <label>Ім'я: <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="text" name="name" id="reg-name" value="<?= $_reg_name; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
            </div>
            <div class="input">
                <label>По-батьковi <br>
                    <input class="txt form-txt-input" type="text" name="fathername" id="reg-fathername" value="<?= $_reg_fathername; ?>">
                </label>
            </div>
            <div class="input">
                <label>Електронна пошта <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="email" name="email" id="reg-email" value="<?= $_reg_email; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
            </div>
            <div class="input">
                <label>Телефон <span class="star-required" title="обов'язкове поле">*</span><br>
                    <input onblur="setTimeout(function(){registration_ckeck_empty_fileld($('#reg-phone'))}, 200);" required="required" class="txt form-txt-input" placeholder="+380" type="text" name="phone" id="reg-phone" value="<?= $_reg_phone; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
            </div>
            <div class="input pass">
                <label>Пароль <span class="star-required" title="обов'язкове поле">*</span> <span class="hint">(не менше 6 символiв)</span><br>
                    <span class="eye" onclick="registration_show_password();"></span>
                    <span id="registration-password-box">
                        <input style="display:block;" onblur="registration_ckeck_empty_fileld_password(this);" required="required" class="txt form-txt-input" id="reg-password" type="password" name="password">
                        <input style="display:none;" onblur="registration_ckeck_empty_fileld_password(this);" class="txt form-txt-input" id="reg-password-replica" type="text">
                    </span>
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
            </div>
            <div class="input">
                <button class="btn green bold">Зареєструватися</button>
            </div>
        </form>
    </div>
    <?php require_once(ROOT . '/protected/scripts/cabinet/info-block.php'); ?>
</div>

<script type="text/javascript">
$(function($){
    $("#reg-phone").mask("+999(99)999-99-99").val('<?= $_reg_phone; ?>');
});
</script>
