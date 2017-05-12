<div class="h1-line">
    <h1>Відновлення доступу</h1>
    <div class="already-have">
        <a href="<?= BASE_URL; ?>/cabinet/registration/">Зареєструватися</a> <br>
        <a href="<?= BASE_URL; ?>/cabinet/login/">Увійти</a>
    </div>
</div>
<?php
    if (isset($__route_result['values']['section'])) {
        // на самом деле это код сброса, а не section
        $restore_code = $__route_result['values']['section'];
        require_once(ROOT . '/protected/scripts/cabinet/restore/second-step.php');
        return;
    }
    
    if (isset($_SESSION['restore']['status']) && !$_SESSION['restore']['status']) {
        ?>
        <h2 class="big-error-message"><?= $_SESSION['restore']['error']['text']; ?></h2>
        <?php
        unset($_SESSION['restore']['status']);
    } elseif (isset($_SESSION['restore']['status'])) {
        ?>
        <h2 class="big-success-message">Цей крок пройдено</h2> <br>
        <?= $_SESSION['restore']['success_text']; ?>
        <?php
        if ($_SESSION['restore']['email_link']) {
            ?><br> Перейти до <a href="<?= $_SESSION['restore']['email_link']['link']; ?>"><?= $_SESSION['restore']['email_link']['title']; ?></a> <?php
        }
        unset($_SESSION['restore']['status']);
        return;
    }


    $_email = (isset($_SESSION['restore']['email']))
        ? $_SESSION['restore']['email']
        : ((isset($_SESSION['login']['email'])) ? $_SESSION['login']['email'] : '');
    $_email = htmlspecialchars($_email, ENT_QUOTES);
?>
<div class="registration">
    <div class="form-block">
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/restore/">
            <div class="input login-form-email">
                <div class="bracket">або</div>
                <label><span>Електронна пошта / логін </span> <br>
                    <input class="txt form-txt-input" type="text" name="email" value="<?= $_email; ?>">
                </label>
            </div>
            <div class="input">
                <label>Телефон <br>
                    <input class="txt form-txt-input" placeholder="+380" type="text" name="phone" id="login-phone" value="<?= $_phone; ?>">
                </label>
            </div>
            <div class="input">
                <button class="btn green bold">Далі</button>
            </div>
        </form>
    </div>
</div>
