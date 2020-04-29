
<h3 class="form__title">
    Видалення профілю
</h3>
<label class="form__label form__label--delete-profile">
    Коментар <span class="hint">(необов’язково)</span> <br>
    <textarea style="width: 95%; height: 150px;"
              class="form__textarea"
              name="comment"
              placeholder="Опишіть, що Вам не сподобалось"></textarea>
</label>
<div class="form form__group form__group--delete-profile">
    <a href="<?= BASE_URL; ?>/cabinet/settings/info/"
       class="btn btn-green btn-md button button__form button__form--register button__form--register--outer">
        Скасувати
    </a>
    <button class="button button__form button__form--registration">
        Видалити
    </button>
</div>
