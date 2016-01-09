<div class="form-block full-width edit-object-form">
    <form action="<?= BASE_URL; ?>/post/cabinet/object-item/edit/" method="post">
        <div class="form-subtitle"><?= htmlspecialchars($object['address'], ENT_QUOTES); ?></div>
        <div class="check-box-line check-box-line-email">
            <span class="niceCheck <?= ($object['notify']) ? 'checked' : ''; ?>" id="checkbox_notify_house"><input value="1" type="checkbox" <?= ($object['notify']) ? 'checked' : ''; ?> name="notify_house"></span>
            <label onclick="$('#checkbox_notify_house').click();">
                Отримувати листи з рахунками-повідомленнями для цього об’єкта
            </label>
        </div>
        <div class="input edit-object-title">
            <label>Назва об’єкту: <br>
                <input autofocus class="txt" type="text" name="object-title" value="<?= htmlspecialchars($object['title'], ENT_QUOTES); ?>">
            </label>
        </div>
        <div class="form-subtitle delete-object">Видалення об’єкта</div>
        <div class="check-box-line">
            <span id="confirm_delete_object" class="niceCheck"><input name="delete_object" value="1" type="checkbox"></span>
            <label onclick="$('#confirm_delete_object').click();">
                Видалити об’єкт з профілю
            </label>
        </div>
        <div class="input with-btn">
            <input type="hidden" name="flat_id" value="<?= $object['id']; ?>">
            <button class="btn big green bold">Зберегти</button>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
    $(".niceCheck").click(function() {
        changeCheck($(this), 'check-group');
    });
});
</script>
