<form class="form-welcome" action="<?= BASE_URL; ?>/post/cabinet/object-item/edit/" method="post">
    <h2 class="form-subtitle"><?= htmlspecialchars($object['address'], ENT_QUOTES); ?></h2>

    <div class="form-group">
        <input class="form-txt" placeholder="Нова назва об’єкту" name="object-title" value="<?= htmlspecialchars($object['title'], ENT_QUOTES); ?>" type="text">
    </div>
    <h3 class="form-subtitle delete-object">Видалення об’єкта</h3>
    <div class="form-group">
        <label class="checkbox green">
            <input name="delete_object" value="1" type="checkbox" class="check-toggle"><span></span>
            Видалити об’єкт з профілю
        </label>
    </div>
    <div class="input with-btn">
        <input type="hidden" name="flat_id" value="<?= $object['id']; ?>">
        <button class="btn ">Зберегти</button>
    </div>
</form>
