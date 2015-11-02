<div class="input">
    <label>Виберiть вулицю: <br>
        <input required="required" autofocus="autofocus" class="txt form-txt-input" type="text" name="street" id="add_obj_street" value="">
    </label>
    <div class="hint-blue">
        Введіть перші літери вулиці та обов'язково виберіть її зі списку
    </div>
</div>
<div class="input house-input">
    <label>Номер будинку <br> <div class="hint-blue"><a onclick="return false;">Немає номера будинку у списку?</a></div>
        <select class="txt" name="house" disabled="disabled" id="add_obj_house">
            <option>-- виберіть --</option>
        </select>
    </label>
</div>
<div class="input">
    <label>Номер квартири <br>
        <select class="txt" name="flat" disabled="disabled" id="add_obj_flat">
            <option>-- виберіть --</option>
        </select>
    </label>
</div>
<div class="input">
    <label>Ключ авторизації <br> <div class="hint-blue"><a rel="prettyPhoto" href="<?= BASE_URL; ?>/db_pic/images/wm/image_20-10-2015_[1445345532].jpg">Де знаходиться ключ авторизації?</a></div>
        <input required="required" style="text-transform:uppercase;" disabled="disabled" class="txt form-txt-input" autocomplete="off" type="text" name="auth_key" id="add_obj_auth_key" value="">
    </label>
</div>
<?php
    $disabled = (Authorization::isLogin() && (Flat::getFlatCount() >= Flat::MAX_USER_FLATS));
?>
<div class="input">
    <button <?= ($disabled) ? 'disabled' : ''; ?> class="btn green bold"><div class="icon-objects"></div>Додати об'єкт</button>
</div>
<?php
    if ($disabled) {
        ?>
        <div class="error-description">
           <br> <?= ERROR_TOO_MANY_FLATS; ?>
        </div>
        <?php
    }
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#add_obj_street").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '<?= BASE_URL; ?>/ajax/json/streets',
                    type: "GET",
                    data: {request: request.term},
                    dataType: "json",
                    success: function(data) {
                        response(data);
                    },
                });
            },
            select: function(event, ui){
                $('#add_obj_flat').html('<option>-- виберіть --</option>').attr('disabled', true);
                $('#add_obj_auth_key').attr('disabled', true);
                _selected_street_id = ui.item.id;

                $.ajax({
                    url: '<?= BASE_URL; ?>/ajax/json/houses',
                    type: "GET",
                    data: {street_id: _selected_street_id},
                    dataType: "json",
                    success: function(data) {
                        var select_options = '';
                        for (var i = 0; i < data.length; i++)
                            select_options += '<option value="'+ data[i].id +'">'+ data[i].label +'</option>';
                        $('#add_obj_house').html(select_options).attr('disabled', false).change();
                    },
                });
            },
        });

        $("#add_obj_house").change(function(){
            $.ajax({
                url: '<?= BASE_URL; ?>/ajax/json/flats',
                type: "GET",
                data: {street_id: _selected_street_id, house_id: $("#add_obj_house").val()},
                dataType: "json",
                success: function(data) {
                    var select_options = '';
                    for (var i = 0; i < data.length; i++)
                        select_options += '<option value="'+ data[i].id +'">'+ data[i].label +'</option>';
                    $('#add_obj_flat').html(select_options).attr('disabled', false);
                    $('#add_obj_auth_key').html(select_options).attr('disabled', false);
                },
            });
        });
        $.mask.definitions['r'] = '[A-Z,a-z,0-9а-яёА-ЯЁ]';
        $("#add_obj_auth_key").mask("rrrr-rrrr-rrrr");
    });
</script>
