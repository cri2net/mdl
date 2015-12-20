<div class="form-block left">
    <div class="input">
        <label>Прiзвище <span class="star-required" title="обов'язкове поле">*</span><br>
            <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="text" name="lastname" id="reg-lastname" value="<?= htmlspecialchars($__userData['lastname'], ENT_QUOTES); ?>">
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
    </div>
    <div class="input">
        <label>Ім'я: <span class="star-required" title="обов'язкове поле">*</span><br>
            <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="text" name="name" id="reg-name" value="<?= htmlspecialchars($__userData['name'], ENT_QUOTES); ?>">
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
    </div>
    <div class="input">
        <label>По-батьковi <br>
            <input class="txt form-txt-input" type="text" name="fathername" id="reg-fathername" value="<?= htmlspecialchars($__userData['fathername'], ENT_QUOTES); ?>">
        </label>
    </div>
</div>
<div class="form-block right">
    <div class="input">
        <label>Електронна пошта <span class="star-required" title="обов'язкове поле">*</span><br>
            <input onblur="registration_ckeck_empty_fileld(this);" required="required" class="txt form-txt-input" type="email" name="email" id="reg-email" value="<?= htmlspecialchars($__userData['email'], ENT_QUOTES); ?>">
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
    </div>
    <div class="input">
        <label>Телефон <span class="star-required" title="обов'язкове поле">*</span><br>
            <input onblur="setTimeout(function(){registration_ckeck_empty_fileld($('#reg-phone'))}, 200);" required="required" class="txt form-txt-input" placeholder="+380" type="text" name="mob_phone" id="reg-phone" value="<?= htmlspecialchars($__userData['mob_phone'], ENT_QUOTES); ?>">
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
    </div>
    <div class="input with-btn">
        <button class="btn big green bold">Зберегти</button>
    </div>
</div>
<script type="text/javascript">
$(function($){
    $("#reg-phone").mask("+999(99)999-99-99").val('<?= htmlspecialchars($__userData['mob_phone'], ENT_QUOTES); ?>');
});
</script>