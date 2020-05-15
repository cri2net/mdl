<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container">
    <content>
        <form method="post"
              action="<?= BASE_URL; ?>/post/cabinet/login/"
              class="form form__login form__login--outer">
            <?php
                if (defined('SHOW_NEED_AUTH_MESSAGE') && SHOW_NEED_AUTH_MESSAGE) {
                    ?><div class="alert alert-warning form__warning form__warning--outer">Для доступу до сторінки необхідно увійти до системи</div> <?php
                }

                if (isset($_SESSION['login']['status']) && !$_SESSION['login']['status']) {
                    ?>
                    <div class="alert alert-warning form__warning form__warning--outer"><?= $_SESSION['login']['error']['text']; ?></div>
                    <?php
                    unset($_SESSION['login']['status']);
                }

                $login = (isset($_SESSION['login']['login'])) ? $_SESSION['login']['login'] : '';
                $login = htmlspecialchars($login, ENT_QUOTES);

                ?>
                <div class="form__group form__group--outer">
                    <input autofocus
                           placeholder="Введіть телефон або пошту"
                           name="email"
                           value="<?= $login; ?>"
                           type="text"
                           class="form__input">
                </div>
                <div class="form__group form__group--outer">
                    <input placeholder="Ваш пароль"
                           name="password"
                           value=""
                           type="password"
                           class="form__input">
                </div>
                <div class="form__group form__group--login">
                    <button class="button button__form button__form--outer">
                        Вхід
                    </button>
                    <a href="<?= BASE_URL; ?>/cabinet/restore/"
                       class="form__link form__link--login">
                        Забули пароль?
                    </a>
                </div>
                <?php
            ?>
        </form>
        <a href="<?= BASE_URL; ?>/cabinet/registration/"
           class="button __fobuttonrm button__form--register button__form--register--outer">
            Зареєструватися у системі
        </a>
    </content>
</div>
