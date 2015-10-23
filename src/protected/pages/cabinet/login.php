<div class="h1-line">
    <h1>Вхід</h1>
    <div class="already-have">
        <a href="<?= BASE_URL; ?>/cabinet/registration/">Зареєструватися</a> <br>
        <a href="<?= BASE_URL; ?>/cabinet/restore/">Забули пароль?</a>
    </div>
</div>
<?php
    if (defined('SHOW_NEED_AUTH_MESSAGE') && SHOW_NEED_AUTH_MESSAGE) {
        ?><h2 class="big-error-message">Для доступу до сторінки необхідно увійти до системи</h2> <?php
    }

    if (isset($_SESSION['login']['status']) && !$_SESSION['login']['status']) {
        ?>
        <h2 class="big-error-message">Під час авторизації виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['login']['error']['text']; ?></div>
        <?php
        unset($_SESSION['login']['status']);
    }

    if (Authorization::isLogin()) {
        ?><h2 class="big-success-message">Ви вже увійшли в систему</h2> <?php
        return;
    }

    $_email = (isset($_SESSION['login']['email'])) ? $_SESSION['login']['email'] : '';
    $_phone = (isset($_SESSION['login']['phone'])) ? $_SESSION['login']['phone'] : '';

    $_email = htmlspecialchars($_email, ENT_QUOTES);
    $_phone = htmlspecialchars($_phone, ENT_QUOTES);
?>
<div class="registration">
    <div class="form-block">
        <form onsubmit="registration_form_submit();" method="post" action="<?= BASE_URL; ?>/post/cabinet/login/">
            <div class="input login-form-email">
                <div class="bracket">або</div>
                <label><span style="cursor:help;" title="Ви можете використовувати обліковий запис (логін, пароль) який ви використовували на personal-account.kiev.ua">Електронна пошта / логiн </span> <br>
                    <input class="txt form-txt-input" type="text" name="email" value="<?= $_email; ?>" style="cursor:help;" title="Ви можете використовувати обліковий запис (логін, пароль) який ви використовували на personal-account.kiev.ua">
                </label>
            </div>
            <div class="input">
                <label>Телефон <br>
                    <input class="txt form-txt-input" placeholder="+380" type="text" name="phone" id="login-phone" value="<?= $_phone; ?>">
                </label>
            </div>
            <div class="input pass">
                <label>Пароль <span class="hint">(не менше 6 символiв)</span> <br>
                    <span class="eye" onclick="registration_show_password();"></span>
                    <span id="registration-password-box">
                        <input style="display:block;" onblur="registration_ckeck_empty_fileld_password(this);" required="required" class="txt form-txt-input" id="reg-password" type="password" name="password">
                        <input style="display:none;" onblur="registration_ckeck_empty_fileld_password(this);" class="txt form-txt-input" id="reg-password-replica" type="text" autocomplete="off">
                    </span>
                </label>
            </div>
            <div class="input">
                <button class="btn green bold">Увійти в систему</button>
            </div>
        </form>
    </div>
    <?php require_once(ROOT . '/protected/scripts/cabinet/info-block.php'); ?>
</div>

<script type="text/javascript">
$(function($){
    $("#login-phone").mask("+999(99)999-99-99").val('<?= $_phone; ?>');
});
</script>
