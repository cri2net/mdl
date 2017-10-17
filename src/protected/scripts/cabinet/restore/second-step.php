<?php
    if ($_SESSION['restore-secont-step']['status']) {
        ?>
        <div class="alert alert-success">Пароль успішно змінено</div>
        <br> Ви були автоматично авторизовані у системі
        <?php
        return;
    } elseif (Authorization::isLogin()) {
        ?><div class="alert alert-success">Ви вже увійшли до системи</div> <?php
        return;
    }

    if (isset($_SESSION['restore-secont-step']['status']) && !$_SESSION['restore-secont-step']['status']) {
        ?>
        <div class="alert alert-danger"><?= $_SESSION['restore-secont-step']['error']['text']; ?></div>
        <?php
        unset($_SESSION['restore-secont-step']['status']);
    }

    try {
        Authorization::verifyUserCode($restore_code);
    } catch (Exception $e) {
        ?>
        <div class="alert alert-danger"><?= $e->getMessage(); ?></div>
        <?php
        return;
    }
?>
        <form class="form-welcome" onsubmit="top.postMessage('login-form-send', 'http://cks.com.ua');" method="post" action="<?= BASE_URL; ?>/post/cabinet/restore-set-password/">
            <div class="form-group">
                <label>Новий пароль <br>
                    <span class="eye" onclick="registration_show_password();"></span>
                    <span id="registration-password-box">
                        <input style="display:block;" class="txt form-txt-input" id="reg-password" type="password" name="new_password">
                        <input style="display:none;" class="txt form-txt-input" id="reg-password-replica" type="text" autocomplete="off">
                    </span>
                </label>
            </div>
            <div class="form-group">
                <input type="hidden" name="code" value="<?= htmlspecialchars($restore_code, ENT_QUOTES); ?>">
                <button class="btn btn-green-bordered">Змінити пароль</button>
            </div>
        </form>
