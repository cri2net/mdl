
<label>Прізвище <span class="star-required" title="обов'язкове поле">*</span><br>
    <input required type="text" name="lastname" value="<?= htmlspecialchars($__userData['lastname'], ENT_QUOTES); ?>">
</label>

<label>Ім’я: <span class="star-required" title="обов'язкове поле">*</span><br>
    <input required type="text" name="name" value="<?= htmlspecialchars($__userData['name'], ENT_QUOTES); ?>">
</label>

<label>По батькові <br>
    <input type="text" name="fathername" value="<?= htmlspecialchars($__userData['fathername'], ENT_QUOTES); ?>">
</label>

<label>Електронна пошта <span class="star-required" title="обов'язкове поле">*</span><br>
    <input required type="email" name="email" value="<?= htmlspecialchars($__userData['email'], ENT_QUOTES); ?>">
</label>

<label>Телефон <span class="star-required" title="обов'язкове поле">*</span><br>
    <input required placeholder="+380" type="text" name="mob_phone" id="reg-phone" value="<?= htmlspecialchars($__userData['mob_phone'], ENT_QUOTES); ?>">
</label>

<div class="form-subtitle">Зміна логіну</div>
<label>Логін: <br>
    <input autocomplete="off" type="text" name="login" value="<?= htmlspecialchars($__userData['login'], ENT_QUOTES); ?>">
</label>



<div class="form-subtitle">Зміна паролю</div>
<div class="input">
    <label>Дійсний пароль <br>
        <input style="display:block;" type="password" name="password">
    </label>
</div>
<label>Новий пароль <br>
    <input style="display:block;" id="reg-password" type="password" name="new_password">
</label>



<div class="form-subtitle delete-profile">Видалення профілю</div>
<div class="check-box-line">
    <span class="niceCheck" id="confirm_delete_profile"><input onclick="$('#confirm_delete_profile').click();" type="checkbox"></span>
    <label onclick="$('#confirm_delete_profile').click();">
        Я розумію наслідки видалення профілю
    </label>
</div>
<a onclick="return check_delete_profile();" href="<?= BASE_URL; ?>/cabinet/settings/delete_profile/">Видалити профіль</a>

<button class="btn btn-blue btn-md">Зберегти</button>

<script>
    $(document).ready(function(){
        $(".niceCheck").click(function() {
            changeCheck($(this), 'check-group');
        });
    });

    $(function($){
        $("#reg-phone").mask("+389(99)999-99-99", {autoclear: false}).val('<?= htmlspecialchars($__userData['mob_phone'], ENT_QUOTES); ?>');
    });
</script>
