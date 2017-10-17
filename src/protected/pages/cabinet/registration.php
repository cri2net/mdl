<?php
    define('SHORT_FOOTER', true);
?>
<body class="login-bg">

<div class="container">
    <content>
        <div class="logo-large">
            <a href="<?= BASE_URL ?>" ><img src="<?= BASE_URL; ?>/assets/images/logo-large.png"></a>
        </div>
        <form class="form-welcome" onsubmit="top.postMessage('register-form-send', 'http://cks.com.ua');" method="post" action="<?= BASE_URL; ?>/post/cabinet/registration/">
            <a href="<?= BASE_URL; ?>/cabinet/login/" class="close">&times;</a>

            <?php
                if (isset($_SESSION['registration']['status'])) {
                    ?>
                    <h3 class="big-error-message"><?= $_SESSION['registration']['error']['text']; ?></h3>
                    <?php
                    unset($_SESSION['registration']['status']);
                } elseif (isset($_SESSION['registration']['show_message'])) {
                    ?>
                    <h3 class="big-<?= $_SESSION['registration']['show_message']['type']; ?>-message"><?= $_SESSION['registration']['show_message']['text']; ?></h3>
                    <?php
                    // наверно надо каждый раз это показывать
                    // unset($_SESSION['registration']['show_message']);
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

                if (Authorization::isLogin()) {
                    ?><h3 class="big-success-message">Ви вже зареєстровані</h3> <?php
                } else {
                    ?>
                    <div class="form-group">
                        <input class="form-txt" autofocus required="required" placeholder="Прізвище" name="lastname" value="<?= $_reg_lastname; ?>" type="text">
                    </div>
                    <div class="form-group">
                        <input class="form-txt" required="required" placeholder="Ім’я" name="name" value="<?= $_reg_name; ?>" type="text">
                    </div>
                    <div class="form-group">
                        <input class="form-txt" placeholder="По-батькові" name="fathername" value="<?= $_reg_fathername; ?>" type="text">
                    </div>
                    <div class="form-group">
                        <input class="form-txt" required="required" placeholder="Електронна пошта" name="email" value="<?= $_reg_email; ?>" type="email">
                    </div>
                    <div class="form-group">
                        <input class="form-txt phone-mask" required="required" placeholder="Телефон" name="phone" id="reg-phone" value="<?= $_reg_phone; ?>" type="text">
                    </div>
                    <div class="form-group">
                        <input class="form-txt" required="required" placeholder="Ваш пароль" name="password" value="" type="password">
                        <span class="eye" onclick="registration_show_password();"></span>
                    </div>
                    <div class="form-group align-left" style="margin-bottom: 20px;">
                        <span class="hint">
                            Реєструючись, Ви підтверджуєте свою згоду на обробку персональних даних, а також підтверджуєте те,
                            що ознайомились та погоджуєтесь з <a href="<?= BASE_URL ?>/services_list_and_docs/docs/user_agreement/" target="_blank">Угодою&nbsp;користувача</a>
                            та <a href="<?= BASE_URL ?>/services_list_and_docs/docs/personal_data/" target="_blank">Згодою на збір та обробку персональних даних</a>
                        </span>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-green-bordered">Зареєструватися</button>
                    </div>
                    <?php
                }
            ?>
        </form>
    </content>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#reg-phone").mask("+999(99)999-99-99", {autoclear: false});
    });
</script>
