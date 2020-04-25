<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container">
    <content>
        <?php
            if (isset($__route_result['values']['section'])) {
                // на самом деле это код сброса, а не section
                $restore_code = $__route_result['values']['section'];
                require_once(ROOT . '/protected/scripts/cabinet/restore/second-step.php');
                return;
            }
        ?>
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/restore/">
            <?php    
                if (isset($_SESSION['restore']['status']) && !$_SESSION['restore']['status']) {
                    ?>
                    <div class="alert alert-danger"><?= $_SESSION['restore']['error']['text']; ?></div>
                    <?php
                    unset($_SESSION['restore']['status']);
                } elseif (isset($_SESSION['restore']['status'])) {
                    ?>
                    <div class="alert alert-success">Цей крок пройдено</div> <br>
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
            <div class="form-group">
                <label><span>Електронна пошта / логін </span> <br>
                    <input type="text" name="email" value="<?= $_email; ?>">
                </label>
            </div>
            <div class="bracket">або</div>
            <div class="form-group">
                <label>Телефон <br>
                    <input placeholder="+380" type="text" name="phone" value="<?= $_phone; ?>">
                </label>
            </div>
            <div class="form-group">
                <button>Далі</button>
            </div>
        </form>
        <a href="<?= BASE_URL; ?>/cabinet/registration/">Зареєструватися у системі</a>
        <a href="<?= BASE_URL; ?>/cabinet/">Увiйти до системи</a>
    </content>
</div>
