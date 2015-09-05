<div class="form-block full-width">
    <div class="check-box-line">
        <span class="niceCheck" id="checkbox_notify_email"><input type="checkbox" name="notify_email"></span>
        <label onclick="$('#checkbox_notify_email').click();">
            Отримувати листи з новинами
        </label>
    </div>
    <div class="all-objects-checkbox">
        <div class="check-box-line">
            <span class="niceCheck check-group-rule" id="check-group-rule"><input type="checkbox" name="notify_email"></span>
            <label onclick="$('#check-group-rule').click();">
                Отримувати листи з рахунками-повідомленнями для усіх об'єктів
            </label>
        </div>
        <div class="checkboxes">
            <div class="check-box-line">
                <span class="niceCheck check-group" id="checkbox_notify_object_100000001"><span class="dotted-line"></span><input type="checkbox" name="notify_email"></span>
                <label onclick="$('#checkbox_notify_object_100000001').click();">
                    Мiй дiм <br>
                    <span class="hint">Київ, ВУЛ. БАНКОВА 1/10</span>
                </label>
            </div>
            <div class="check-box-line">
                <span class="niceCheck check-group" id="checkbox_notify_object_100000002"><span class="dotted-line"></span><input type="checkbox" name="notify_email"></span>
                <label onclick="$('#checkbox_notify_object_100000002').click();">
                    Моя квартира <br>
                    <span class="hint">Київ, ВУЛ. ТАРАСА ШЕВЧЕНКА, кв. 22</span>
                </label>
            </div>
            <div class="check-box-line">
                <span class="niceCheck check-group" id="checkbox_notify_object_100000003"><span class="dotted-line"></span><input type="checkbox" name="notify_email"></span>
                <label onclick="$('#checkbox_notify_object_100000003').click();">
                    Квартира жiнки <br>
                    <span class="hint">Київ, ПЛ. IВАНА ФРАНКО, 45, кв. 120</span>
                </label>
            </div>
        </div>
    </div>
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
