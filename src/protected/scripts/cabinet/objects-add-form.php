<?php
    use cri2net\php_pdo_db\PDO_DB;

    $cities = PDO_DB::table_list(DB_TBL_CITIES, '', 'pos ASC');
?>
<div class="input">
    <label>Виберіть місто *: <br>
        <select class="txt" name="city" id="select_city_id">
            <?php
                foreach ($cities as $city) {
                    ?>
                    <option value="<?= $city['id']; ?>"><?= htmlspecialchars($city['name_ua']); ?></option>
                    <?php
                }
            ?>
        </select>
    </label>
</div>
<div class="input">
    <label>Виберіть вулицю: <br>
        <input required="required" autofocus="autofocus" class="txt form-txt-input" type="text" name="street" id="add_obj_street" value="">
    </label>
    <div class="hint-blue">
        Введіть перші літери вулиці та обов’язково виберіть її зі списку
    </div>
</div>
<div class="input house-input">
    <label>Номер будинку <br>
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
<div class="input" id="tenant_div" style="display: none;">
    <label>Наймач <br>
        <select class="txt" style="width: 350px;" name="tenant" id="tenant">
        </select>
    </label>
</div>
<div class="input" id="pin-code" style="display: none">
    <label>Перевірочний код<br/>
        <input id="add_obj_pin" name="pin" class="txt" type="text" required="required" />
    </label>
    <div class="hint-blue">Введіть перевірочний PIN-код, який ми відправили Вам на пошту</div>
</div>
<?php
    $disabled = (Authorization::isLogin() && (Flat::getFlatCount() >= Flat::getMaxUserFlats()));
?>
<div class="input align-center">
    <button <?= ($disabled) ? 'disabled' : ''; ?> class="btn btn-blue"><span class="fa fa-check"></span>Додати об’єкт</button>
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
    var PIN_SENT = false;

    $(document).ready(function() {
        $("#add_obj_street").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '<?= BASE_URL; ?>/ajax/json/streets',
                    type: "GET",
                    data: {
                        request: request.term,
                        city_id: $('#select_city_id').val()
                    },
                    dataType: "json",
                    success: function(data) {
                        response(data);
                    },
                });
            },
            select: function(event, ui){
                $('#add_obj_flat').html('<option>-- виберіть --</option>').attr('disabled', true);
                _selected_street_id = ui.item.id;

                $.ajax({
                    url: '<?= BASE_URL; ?>/ajax/json/houses',
                    type: "GET",
                    data: {street_id: _selected_street_id},
                    dataType: "json",
                    success: function(data) {
                        var select_options = '';
                        for (var i = 0; i < data.length; i++) {
                            select_options += '<option value="'+ data[i].id +'">'+ data[i].label +'</option>';
                        }
                        $('#add_obj_house').html(select_options).attr('disabled', false).trigger('change');
                    },
                });
            },
        });

        $("#add_obj_house").change(function(){
            $.ajax({
                url: '<?= BASE_URL; ?>/ajax/json/flats',
                type: "GET",
                data: {
                    street_id: _selected_street_id,
                    house_id: $("#add_obj_house").val(),
                    city_id: $('#select_city_id').val()
                },
                dataType: "json",
                success: function(data) {
                    var select_options = '';
                    for (var i = 0; i < data.length; i++) {
                        select_options += '<option value="'+ data[i].id +'">'+ data[i].label +'</option>';
                    }
                    $('#add_obj_flat').html(select_options).attr('disabled', false).trigger('change');
                },
            });
        });

        $("#add_obj_flat").change(function(){

            var flat = $("#add_obj_flat").val();
            PIN_SENT = false;
            $('#pin-code').hide();

            if ((flat == '') || (flat == '0')) {
                $('#tenant').html('');
                $('#tenant_div').css('display', 'none');
                return;
            }

            $.ajax({
                url: '<?= BASE_URL; ?>/ajax/json/tenants/',
                type: "GET",
                data: {
                    flat_id: $("#add_obj_flat").val(),
                },
                dataType: "json",
                success: function(response) {

                    if (response.list.length > 1) {
                        var select_options = '';
                        for (var i = 0; i < response.list.length; i++) {
                            select_options += '<option value="'+ response.list[i].platcode +'">'+ response.list[i].name +'</option>';
                        }
                        $('#tenant').html(select_options).attr('disabled', false);
                        $('#tenant_div').css('display', '');

                    } else {
                        $('#tenant').html('');
                        $('#tenant_div').css('display', 'none');
                    }
                },
            });
        });

        $.mask.definitions['r'] = '[A-Z,a-z,0-9а-яёА-ЯЁ]';
        $("#add_obj_auth_key").mask("rrrr-rrrr-rrrr");

        $("#add-object-form>form").on('submit', function(){

            if (PIN_SENT == true) {
                return true;
            }

            $.ajax({
                url: '<?= BASE_URL ?>/ajax/json/flat-pin/',
                type: "POST",
                data: {
                    id_flat: $("#add_obj_flat").val()
                },
                dataType: "json",
                success: function(response) {
                    switch (response.result) {
                        case 'ok':
                            
                            PIN_SENT = true;

                            $('#pin-code').show();
                            break;

                        case 'error':
                            alert(response.msg);
                            break;
                    }
                }
            });

            $("#pin-code").slideDown();
            return false;
        });
    });
</script>
