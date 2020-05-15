<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');

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
<section class="container registration">
    <content>
        <form method="post"
              action="<?= BASE_URL; ?>/post/cabinet/registration/"
              class="registration__form form form__registration form__registration--outer">
            <a href="<?= BASE_URL; ?>/cabinet/login/"
               class="close form__close-link"></a>

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
                }
            ?>
            <div class="form-group form__group form__group--outer">
                <input autofocus
                       required="required"
                       placeholder="Прізвище"
                       name="lastname"
                       value="<?= $_reg_lastname; ?>"
                       type="text"
                       class="form__input">
            </div>
            <div class="form-group form__group form__group--outer">
                <input required="required"
                       placeholder="Ім’я"
                       name="name"
                       value="<?= $_reg_name; ?>"
                       type="text" class="form__input">
            </div>
            <div class="form-group form__group form__group--outer">
                <input placeholder="По батькові"
                       name="fathername"
                       value="<?= $_reg_fathername; ?>"
                       type="text"
                       class="form__input">
            </div>
            <div class="form-group form__group form__group--outer">
                <input required="required"
                       placeholder="Електронна пошта"
                       name="email"
                       value="<?= $_reg_email; ?>"
                       type="email"
                       class="form__input">
            </div>
            <div class="form-group form__group form__group--outer">
                <input required="required"
                       placeholder="Телефон"
                       name="phone"
                       id="reg-phone"
                       value="<?= $_reg_phone; ?>"
                       type="text" class="form__input">
            </div>
            <div class="form-group form__group form__group--outer">
                <input required="required"
                       placeholder="Ваш пароль"
                       name="password"
                       value=""
                       type="password"
                       class="form__input">
            </div>
            <div class="form-group form__group">
                <button class="button button__form button__form--outer button__form--registration">
                    Зареєструватися
                </button>
            </div>
            <div class="form-group form__group">
                <a href="<?= BASE_URL; ?>/cabinet/login/"
                   class="form__link form__link--login">
                    У мене вже є аккаунт
                </a>
            </div>
        </form>
    </content>
</section>
<script>
    $(function($){
        $("#reg-phone").mask("+389(99)999-99-99", {autoclear: false}).val('<?= htmlspecialchars($_reg_phone, ENT_QUOTES); ?>');
    });
</script>
