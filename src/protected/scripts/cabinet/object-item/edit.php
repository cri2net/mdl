<form class="form form--outer" action="<?= BASE_URL; ?>/post/cabinet/object-item/edit/" method="post">
    <h3 class="form__title"><?= htmlspecialchars($object['address'], ENT_QUOTES); ?></h3>

    <div class="input form__group form__group--outer">
        <label class="form__label">Нова назва об’єкту: <br>
            <input type="text" class="form__input form__input--select" name="object-title" value="<?= htmlspecialchars($object['title'], ENT_QUOTES); ?>">
        </label>
    </div>

    <h3 class="form__title">Видалення об’єкта</h3>
    <div class="check-box-line form__group form__group--outer">
        <input id="delete_object" name="delete_object" value="1" type="checkbox">
        <label for="delete_object">
            Видалити об’єкт з профілю
        </label>
    </div>

    <div class="form__group form__group--outer">
        <input type="hidden" name="flat_id" value="<?= $object['id']; ?>">
        <button class="btn btn-blue btn-md button button__form button__form--registration">Зберегти</button>
    </div>
</form>
