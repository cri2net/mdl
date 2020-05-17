
<div class="input form__group form__group--outer">
    <label class="form__label">Прізвище <span class="star-required" title="обов'язкове поле">*</span><br>
        <input required type="text" class="form__input form__input--select" name="lastname" value="<?= htmlspecialchars($__userData['lastname'], ENT_QUOTES); ?>">
    </label>
</div>

<div class="input form__group form__group--outer">
    <label class="form__label">Ім’я: <span class="star-required" title="обов'язкове поле">*</span><br>
        <input required type="text" class="form__input form__input--select" name="name" value="<?= htmlspecialchars($__userData['name'], ENT_QUOTES); ?>">
    </label>
</div>

<div class="input form__group form__group--outer">
    <label class="form__label">По батькові <br>
        <input type="text" class="form__input form__input--select" name="fathername" value="<?= htmlspecialchars($__userData['fathername'], ENT_QUOTES); ?>">
    </label>
</div>

<div class="input form__group form__group--outer">
    <label class="form__label">Електронна пошта <span class="star-required" title="обов'язкове поле">*</span><br>
        <input required type="email" class="form__input form__input--select" name="email" value="<?= htmlspecialchars($__userData['email'], ENT_QUOTES); ?>">
    </label>
</div>

<div class="input form__group form__group--outer">
    <label class="form__label">Телефон <span class="star-required" title="обов'язкове поле">*</span><br>
        <input required class="form__input form__input--select" placeholder="+380" type="text" name="mob_phone" id="reg-phone" value="<?= htmlspecialchars($__userData['mob_phone'], ENT_QUOTES); ?>">
    </label>
</div>

<h3 class="form-subtitle form__title">Зміна паролю</h3>
<div class="input  form__group form__group--outer">
    <label class="form__label">Дійсний пароль <br>
        <input style="display:block;" type="password" class="form__input form__input--select" name="password">
    </label>
</div>

<div class="input form__group form__group--outer">
    <label class="form__label">Новий пароль <br>
        <input style="display:block;" id="reg-password" type="password" class="form__input form__input--select" name="new_password">
    </label>
</div>

<h3 class="form-subtitle delete-profile form__title">Видалення профілю</h3>
<div class="check-box-line form__group form__group--outer">
    <input id="confirm_delete_profile" type="checkbox">
    <label for="confirm_delete_profile">
        Я розумію наслідки видалення профілю
    </label>
</div>
<a class="button button__form button__form--register button__form--register--outer" onclick="return check_delete_profile();" href="<?= BASE_URL; ?>/cabinet/settings/delete_profile/">Видалити профіль</a>

<div class="form__group form__group--outer">
    <button class="btn btn-blue btn-md button button__form button__form--registration">Зберегти</button>
</div>

<script>
    $(function($){
        $("#reg-phone").mask("+389(99)999-99-99", {autoclear: false}).val('<?= htmlspecialchars($__userData['mob_phone'], ENT_QUOTES); ?>');
    });
</script>
