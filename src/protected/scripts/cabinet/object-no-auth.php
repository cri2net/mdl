<h1 class="big-title">Об’єкти</h1>
<?php
    if (isset($_SESSION['objects']['status']) && !$_SESSION['objects']['status']) {
        ?>
        <br><h2 class="big-error-message"><?= $_SESSION['objects']['error']['text']; ?></h2>
        <?php
        unset($_SESSION['objects']['status']);
    }
?>
<div class="registration object-no-auth">
    <div class="form-block">
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/object-no-auth/">
            <input type="text" name="country" value="" style="display:none;">
            <?php require_once(ROOT . '/protected/scripts/cabinet/objects-add-form.php'); ?>
        </form>
    </div>
    <?php require_once(ROOT . '/protected/scripts/cabinet/info-block.php'); ?>
</div>