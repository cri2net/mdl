<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container">
    <content>
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/login/">
            <?php
                if (defined('SHOW_NEED_AUTH_MESSAGE') && SHOW_NEED_AUTH_MESSAGE) {
                    ?><div class="alert alert-warning" >Для доступу до сторінки необхідно увійти до системи</div> <?php
                }

                if (isset($_SESSION['login']['status']) && !$_SESSION['login']['status']) {
                    ?>
                    <div class="alert alert-danger" ><?= $_SESSION['login']['error']['text']; ?></div>
                    <?php
                    unset($_SESSION['login']['status']);
                }

                $login = (isset($_SESSION['login']['login'])) ? $_SESSION['login']['login'] : '';
                $login = htmlspecialchars($login, ENT_QUOTES);

                ?>
                <div class="form-group">
                    <input autofocus placeholder="Введіть телефон або пошту" name="email" value="<?= $login; ?>" type="text">
                </div>
                <div class="form-group">
                    <input placeholder="Ваш пароль" name="password" value="" type="password">
                </div>
                <div class="form-group">
                    <button>Вхід</button>
                    <a href="<?= BASE_URL; ?>/cabinet/restore/">Забули пароль?</a>
                </div>
                <?php
            ?>
        </form>
        <a href="<?= BASE_URL; ?>/cabinet/registration/">Зареєструватися у системі</a>
    </content>
</div>
