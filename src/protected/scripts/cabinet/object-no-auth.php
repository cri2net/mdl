<h1 class="big-title">Об'єкти</h1>
<div class="registration object-no-auth">
    <div class="form-block">
        <form method="post" action="<?= BASE_URL; ?>/post/cabinet/object-no-auth/">
            <input type="text" name="country" value="" style="display:none;">
            <?php require_once(ROOT . '/protected/scripts/cabinet/objects-add-form.php'); ?>
        </form>
    </div>
    <?php require_once(ROOT . '/protected/scripts/cabinet/info-block.php'); ?>
</div>