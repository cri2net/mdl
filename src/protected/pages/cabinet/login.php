<?php
    define('SHORT_FOOTER', true);
?>
<body class="login-bg">

<div class="container">
    <content>
        <div class="logo-large">
            <a href="<?= BASE_URL ?>" ><img src="<?= BASE_URL; ?>/assets/images/logo-large.png"></a>
        </div>

        <form class="form-welcome" onsubmit="top.postMessage('login-form-send', 'http://cks.kiev.ua');" method="post" action="<?= BASE_URL; ?>/post/cabinet/login/">
            <!--<a href="#" class="close">&times;</a>-->
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

                if (Authorization::isLogin()) {
                    ?><div class="alert alert-success" >Ви вже увійшли в систему</div> <?php
                } else {
                    ?>
                    <div class="form-group">
                        <input class="form-txt" autofocus placeholder="Введіть телефон або пошту" name="email" value="<?= $login; ?>" type="text">
                    </div>
                    <div class="form-group">
                        <input class="form-txt" placeholder="Ваш пароль" name="password" value="" type="password">
                        <span class="eye" onclick="registration_show_password();"></span>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-green-bordered">Вхід</button>
                        <a href="<?= BASE_URL; ?>/cabinet/restore/">Забули пароль?</a>
                    </div>
                    <?php
                }
            ?>
        </form>
        <a href="<?= BASE_URL; ?>/cabinet/registration/" class="btn btn-white-bordered">Зареєструватися у системі</a>
    </content>
</div>
