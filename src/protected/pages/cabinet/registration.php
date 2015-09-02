<div class="h1-line">
    <h1>Реєстрація</h1>
    <a href="<?= BASE_URL; ?>/cabinet/login/" class="already-have">У мене вже є аккаунт</a>
</div>
<?php
    if(Authorization::isLogin())
    {
        ?>
        <h2 class="reg">Вы уже зарегестрированы</h2>
        <?php
        return;
    }
?>
<div class="registration">
    <div class="form-block">
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/registration/">
            <div class="input">
                <label>Ім'я: <br>
                    <input required="required" class="txt form-txt-input" type="text" name="name" id="reg-name" value="">
                </label>
            </div>
            <div class="input">
                <label>По-батьковi <br>
                    <input required="required" class="txt form-txt-input" type="text" name="fathername" id="reg-fathername" value="">
                </label>
            </div>
            <div class="input">
                <label>Прiзвище <br>
                    <input required="required" class="txt form-txt-input" type="text" name="lastname" id="reg-lastname" value="">
                </label>
            </div>
            <div class="input">
                <label>Електронна пошта <br>
                    <input required="required" class="txt form-txt-input" type="email" name="email" id="reg-email" value="">
                </label>
            </div>
            <div class="input">
                <label>Телефон <br>
                    <input required="required" class="txt form-txt-input" type="text" name="phone" id="reg-phone" value="">
                </label>
            </div>
            <div class="input pass">
                <label>Пароль <span class="hint">(не менше 6 символiв)</span> <br>
                    <span class="eye" onclick=""></span>
                    <input required="required" class="txt form-txt-input" id="reg-password" type="password" name="password">
                </label>
            </div>
            <div class="input">
                <button class="btn green bold">Зареєструватися</button>
            </div>
        </form>
    </div>
    <div class="info-block">
        <div class="block-inner">
            <div class="title">Особистий кабiнет — це:</div>
            <div class="item like">можливість оплати комунальних послуг зручно, швидко, без комісії, безпечно</div>
            <div class="item archive">деталізований архів платежів</div>
            <div class="item email">інформування через e-mail про наявність нових квитанцій</div>
            <div class="item news">останні новини ринку ЖКП та аналітичні огляди</div>
            <div class="item first">акції, конкурси та приємні подарунки</div>
            <div class="item quick">миттєві платежі</div>
        </div>
    </div>
</div>

<!--<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery.maskedinput-1.2.2.min.js"></script>
<script type="text/javascript">
jQuery(function($){
   $("#reg-phone").mask("(999)999-99-99");
});
</script>-->