<?php
    if ($_SESSION['restore-secont-step']['status']) {
        ?>
        <h2 class="big-success-message">Пароль успішно змінено</h2>
        <br> Ви були автоматично авторизовані у системі
        <?php
        return;
    } elseif (Authorization::isLogin()) {
        ?><h2 class="big-success-message">Ви вже увійшли до системи</h2> <?php
        return;
    }

    if (isset($_SESSION['restore-secont-step']['status']) && !$_SESSION['restore-secont-step']['status']) {
        ?>
        <h2 class="big-error-message"><?= $_SESSION['restore-secont-step']['error']['text']; ?></h2>
        <?php
        unset($_SESSION['restore-secont-step']['status']);
    }

    try {
        Authorization::verifyUserCode($restore_code);
    } catch (Exception $e) {
        ?>
        <h2 class="big-error-message"><?= $e->getMessage(); ?></h2>
        <?php
        return;
    }
?>
<div class="registration">
    <div class="form-block">
        <form onsubmit="registration_form_submit();" method="post" action="<?= BASE_URL; ?>/post/cabinet/restore-set-password/">
            <div class="input pass-logn">
                <label>Новий пароль <br>
                    <span class="eye" onclick="registration_show_password();"></span>
                    <span id="registration-password-box">
                        <input style="display:block;" class="txt form-txt-input" id="reg-password" type="password" name="new_password">
                        <input style="display:none;" class="txt form-txt-input" id="reg-password-replica" type="text" autocomplete="off">
                    </span>
                </label>
            </div>
            <div class="input">
                <input type="hidden" name="code", value="<?= htmlspecialchars($restore_code, ENT_QUOTES); ?>">
                <button class="btn green bold">Змiнити пароль</button>
            </div>
        </form>
    </div>
    <?php require_once(ROOT . '/protected/scripts/cabinet/info-block.php'); ?>
</div>