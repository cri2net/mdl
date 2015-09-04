<div class="h1-line">
    <h1>Вхід</h1>
    <a href="<?= BASE_URL; ?>/cabinet/login/" class="already-have">Зареєструватися</a>
</div>
<?php
    if (isset($_SESSION['login']['status']) && !$_SESSION['login']['status']) {
        ?>
        <h2 class="big-error-message">При авторизації виникли помилки:</h2>
        <div class="error-desription"><?= $_SESSION['login']['error']['text']; ?></div>
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
                <div class="bracket">abo</div>
                <label>Електронна пошта <br>
                    <input class="txt form-txt-input" type="email" name="email" value="<?= $_email; ?>">
                </label>
            </div>
            <div class="input">
                <label>Телефон <br>
                    <input class="txt form-txt-input" placeholder="+380" type="text" name="phone" id="login-phone" value="<?= $_phone; ?>">
                </label>
                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
            </div>
            <div class="input pass">
                <label>Пароль <span class="hint">(не менше 6 символiв)</span> <br>
                    <span class="eye" onclick="registration_show_password();"></span>
                    <span id="registration-password-box">
                        <input style="display:block;" onblur="registration_ckeck_empty_fileld_password(this);" required="required" class="txt form-txt-input" id="reg-password" type="password" name="password">
                        <input style="display:none;" onblur="registration_ckeck_empty_fileld_password(this);" class="txt form-txt-input" id="reg-password-replica" type="text">
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