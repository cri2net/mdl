<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>
        <div class="portlet">
            <h2>Питання до фахівця</h2><br/>
            <?php
                if (isset($_SESSION['feedback']['status']) && $_SESSION['feedback']['status']) {
                    ?>
                    <div class="alert alert-success">
                        Питання успiшно вiдправлено. Наш фахiвець незабаром вiдповiсть.
                    </div>
                    <?php
                    unset($_SESSION['feedback']);
                } elseif (isset($_SESSION['feedback']['status'])) {
                    ?>
                    <div class="alert alert-success">
                        Під час надсилання повідомлення виникла помилка: <br>
                        <?= $_SESSION['feedback']['error']['text']; ?>
                    </div>
                    <?php
                    unset($_SESSION['feedback']['status']);
                }

                require_once(PROTECTED_DIR . '/pages/feedback/form.php');
            ?>
        </div>
    </content>
</div>
