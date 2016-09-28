<div class="form-block">
    <div class="form-subtitle">Зміна логіну</div>
    <div class="input">
        <label>Логін: <br>
            <input autocomplete="off" class="txt form-txt-input" type="text" name="login" value="<?= htmlspecialchars($__userData['login'], ENT_QUOTES); ?>">
        </label>
    </div>
    <div class="form-subtitle">Зміна паролю</div>
    <div class="input">
        <label>Дійсний пароль <br>
            <input style="display:block;" class="txt form-txt-input" type="password" name="password">
        </label>
    </div>
    <div class="input pass-logn">
        <label>Новий пароль <br>
            <span class="eye" onclick="registration_show_password();"></span>
            <span id="registration-password-box">
                <input style="display:block;" class="txt form-txt-input" id="reg-password" type="password" name="new_password">
                <input style="display:none;" class="txt form-txt-input" id="reg-password-replica" type="text" autocomplete="off">
            </span>
        </label>
    </div>
    <div class="form-subtitle delete-profile">Видалення профілю</div>
    <div class="check-box-line">
        <span class="niceCheck" id="confirm_delete_profile"><input type="checkbox"></span>
        <label onclick="$('#confirm_delete_profile').click();">
            Я розумію наслідки видалення профілю
        </label>
    </div>
    <a onclick="return check_delete_profile();" href="<?= BASE_URL; ?>/cabinet/settings/delete_profile/">Видалити профіль</a>
    <div class="input with-btn">
        <button class="btn big green bold">Зберегти</button>
    </div>
</div>
<script>
    $(document).ready(function(){
        $(".niceCheck").click(function() {
            changeCheck($(this), 'check-group');
        });
    });
</script>
