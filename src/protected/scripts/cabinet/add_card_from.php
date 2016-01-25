<div class="add-card-box">

    <a class="add-card-link" onclick="$('.add-card-form').slideToggle();">Прив’язати нову картку</a>
    <div class="add-card-form" style="display: none;">
        <div class="input">
            <label>
                Соціальный номер
                <span class="hint">на зворотній стороні карти</span>
                <span title="обов’язкове поле" class="star-required">*</span><br>
                <input type="text" value="" id="addcard_card-number" placeholder="1111-2222-3333-4444" class="txt form-txt-input" onblur="setTimeout(function(){registration_ckeck_empty_fileld($('#addcard_card-number'))}, 200);">
            </label>
            <div class="error-text" style="display: none;"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
        <div class="input">
            <label>
                Номер паспорту
                <span class="hint">без серії, лише 6 цифр</span>
                <span title="обов’язкове поле" class="star-required">*</span><br>
                <input type="text" value="" id="addcard_pasp-number" placeholder="123456" class="txt form-txt-input" onblur="setTimeout(function(){registration_ckeck_empty_fileld($('#addcard_pasp-number'))}, 200);">
            </label>
            <div class="error-text" style="display: none;"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
        <div class="input">
            <label>
                Дата народження
                <span class="hint">у форматі dd.mm.yyyy</span>
                <span title="обов’язкове поле" class="star-required">*</span><br>
                <input type="text" value="" id="addcard_birthday" placeholder="24.08.1991" class="txt form-txt-input" onblur="setTimeout(function(){registration_ckeck_empty_fileld($('#addcard_birthday'))}, 200);">
            </label>
            <div class="error-text" style="display: none;"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
        <div class="btn green add-card-btn">Додати картку</div>
        <div class="error-description" style="display: none;" id="addcard_error"></div>
    </div>
</div>
<script type="text/javascript">
$(function($){
    $("#addcard_card-number").mask("9999-9999-9999-9999");
    $("#addcard_pasp-number").mask("999999");
    $("#addcard_birthday").mask("99.99.9999");
    $('.add-card-btn').click(function(){
        add_card();
    });
});
</script>
