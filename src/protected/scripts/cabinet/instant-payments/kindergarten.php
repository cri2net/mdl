<h1 class="big-title">Дитячі садки м.Київ (харчування)</h1>
<br><br>

<?php
    try {
        $districts = (array)Kinders::getFirmeList();

        if (is_array($_SESSION['instant-payments-kinders']['columns'])) {
            foreach ($_SESSION['instant-payments-kinders']['columns'] as $key => $value) {
                $$key = $value;
            }
        }

        if (!isset($_SESSION['instant-payments-kinders']['step'])) {
            $_SESSION['instant-payments-kinders']['step'] = 'region';
        }

        $penalty_user_email      = (isset($__userData['email']))      ? $__userData['email'] : '';
        $penalty_user_name       = (isset($__userData['name']))       ? $__userData['name'] : '';
        $penalty_user_fathername = (isset($__userData['fathername'])) ? $__userData['fathername'] : '';
        $penalty_user_lastname   = (isset($__userData['lastname']))   ? $__userData['lastname'] : '';
        
        $penalty_user_email      = (isset($_SESSION['instant-payments-kinders']['columns']['penalty_user_email']))      ? $_SESSION['instant-payments-kinders']['columns']['penalty_user_email']      : $penalty_user_email;
        $penalty_user_name       = (isset($_SESSION['instant-payments-kinders']['columns']['penalty_user_name']))       ? $_SESSION['instant-payments-kinders']['columns']['penalty_user_name']       : $penalty_user_name;
        $penalty_user_fathername = (isset($_SESSION['instant-payments-kinders']['columns']['penalty_user_fathername'])) ? $_SESSION['instant-payments-kinders']['columns']['penalty_user_fathername'] : $penalty_user_fathername;
        $penalty_user_lastname   = (isset($_SESSION['instant-payments-kinders']['columns']['penalty_user_lastname']))   ? $_SESSION['instant-payments-kinders']['columns']['penalty_user_lastname']   : $penalty_user_lastname;

        if (!isset($id_district) || !$id_district) {
            $id_district = 0;
        }

    } catch (Exception $e) {
        $_SESSION['instant-payments-kinders']['status'] = false;
        $_SESSION['instant-payments-kinders']['error']['text'] = $e->getMessage();
        $_SESSION['instant-payments-kinders']['step'] = 'region';
    }

    if (isset($_SESSION['instant-payments-kinders']['status']) && !$_SESSION['instant-payments-kinders']['status']) {
        ?>
        <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['instant-payments-kinders']['error']['text']; ?></div>
        <?php
        unset($_SESSION['instant-payments-kinders']['status']);
    }

    switch ($_SESSION['instant-payments-kinders']['step']) {
        case 'frame':
            $id = $_SESSION['instant-payments-kinders']['kinders_last_payment_id'];
            $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $id);
            $payment_id = $_payment['id'];
        
            $file = ROOT . "/protected/conf/payments/tas/tas";
            if (file_exists($file . ".conf.php")) {
                require_once($file . ".conf.php");
            }
            if (file_exists($file . ".process.php")) {
                require_once($file . ".process.php");
            }
            if (file_exists($file . ".payform.php")) {
                require_once($file . ".payform.php");
            }

            $_SESSION['instant-payments-kinders']['step'] = 'region';
            break;

        case 'details':
            $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['instant-payments-kinders']['kinders_last_payment_id']);
            $_service = PDO_DB::table_list(ShoppingCart::SERVICE_TABLE, "payment_id='{$_payment['id']}'");
            $_service = $_service[0];
            $_service['data'] = @json_decode($_service['data']);

            require_once(PROTECTED_DIR . '/scripts/cabinet/instant-payments/_payment_details_step.php');
            break;

        case 'region':
        default:
            ?>
            <form class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/kindergarten/">
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Район міста <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <select class="txt" required="required" id="id_district" name="id_district" onchange="kinders_district_onchange(this);" onblur="registration_ckeck_empty_fileld(this);">
                                <option value="">-- Будь ласка, оберіть --</option>
                                <?php
                                    foreach ($districts as $item) {
                                        ?><option <?= ($id_district == $item['id']) ? 'selected="selected"' : ''; ?> value="<?= $item['id']; ?>"><?= htmlspecialchars($item['name']); ?></option> <?php
                                    }
                                ?>
                            </select>
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block;">
                        <input type="hidden" id="final_kindergarten" name="kindergarten" value="<?= $R101; ?>" />
                        <label>
                            Установа <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <?php
                                foreach ($districts as $item) {
                                    ?>
                                    <select class="txt kindergarten_select" required="required" onchange="$('#final_kindergarten').val($(this).val()).trigger('change');" onblur="registration_ckeck_empty_fileld(this);" style="display:<?= ($id_district == $item['id']) ? 'inline-block' : 'none'; ?>;" id="kindergarten_<?= $item['id']; ?>">
                                        <option value="0">-- Будь ласка, оберіть --</option>
                                        <?php
                                            $list = Kinders::getInstitutionList($item['id']);
                                            
                                            foreach ($list as $list_key => $list_item) {
                                                ?>
                                                <option <?= ($R101 == $list_item['R101']) ? 'selected="selected"' : ''; ?> value="<?= $list_item['R101']; ?>"><?= $list_item['NAME_SAD']; ?></option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                    <?php
                                }
                            ?>
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Група / клас <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <select class="txt" required="required" id="kindergarten_class_select" name="child_class" onblur="registration_ckeck_empty_fileld(this);">
                                <option value="">-- Будь ласка, оберіть --</option>
                            </select>
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div title="введіть перші три літери прізвища" class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            ПІБ учня <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" class="txt"  disabled="disabled" type="text" name="child_fio" required="required" style="width:200px;" id="kindergarten_fio_select">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Сума, грн <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($summ, ENT_QUOTES); ?>" type="text" name="summ" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <h3>Платник</h3>

                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            Прізвище <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_lastname, ENT_QUOTES); ?>" type="text" name="penalty_user_lastname" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>

                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Ім’я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_name, ENT_QUOTES); ?>" type="text" name="penalty_user_name" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
                        <label>
                            По-батькові <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_fathername, ENT_QUOTES); ?>" type="text" name="penalty_user_fathername" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                    <div class="field-group" style="display: inline-block;">
                        <label>
                            Електронна пошта <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_email, ENT_QUOTES); ?>" type="email" name="penalty_user_email" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>
                
                <div style="display: inline-block; float: none; width: 100%;">
                    <div class="field-group full-width">
                        <label>
                            Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                            <input style="width: 655px;" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_address, ENT_QUOTES); ?>" type="text" name="penalty_user_address" class="txt" required="required">
                        </label>
                        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                    </div>
                </div>

                <div class="field-group align-center">
                    <button id="submitOrder" class="btn green bold">Далі</button>
                </div>
            </form>
            <?php
    }

?>

<script type="text/javascript">
    $(function(){
        $("#kindergarten_fio_select").autocomplete({
            source: function(request, response){

                var val = $('#kindergarten_class_select').val();

                $.ajax({
                    url : BASE_URL + '/ajax/json/kindergarten',
                    type: "POST",
                    dataType: "json",
                    minLength: 3,
                    data: {
                        action: 'get_children_list',
                        id_rono_group: val,
                        fio: request.term,
                    },
                    success: function(data) {
                        response($.map(data.list, function(item) {
                            return {
                                label: item.name,
                                value: item.name
                            }
                        }));
                    },
                });
            }
        });

        $('#kindergarten_class_select').change(function() {
            var val = $('#kindergarten_class_select').val();
            $('#kindergarten_fio_select').val('');

            if (val == '') {
                $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');
                return;
            }

            $('#kindergarten_fio_select').removeAttr('disabled');
        });

        $('#final_kindergarten').change(function() {
            var val = $('#final_kindergarten').val();
            var first_option = '<option value="">-- Будь ласка, оберіть --</option>';
            $('#kindergarten_class_select').html(first_option).attr('disabled', 'disabled').trigger('change');
            $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');

            if (val == '') {
                return;
            }

            $.ajax({
                dataType: 'json',
                data: {
                    action: 'get_classes_list',
                    id_sad: val
                },
                type: 'POST',
                url : BASE_URL + '/ajax/json/kindergarten',
                success : function(response) {
                    var html = '';
                    for (var i = 0; i < response.list.length; i++) {
                        html += '<option value="'+ response.list[i].id +'">'+ response.list[i].name +'</option>';
                    };
                    $('#kindergarten_class_select').html(html).removeAttr('disabled').trigger('change');
                }
            });
        });
    });

    var id_district = <?= $id_district; ?>;
    function kinders_district_onchange(el)
    {
        var tmp_id_district = $(el).val();

        $('.kindergarten_select').hide();
        $('#kindergarten_'+tmp_id_district).css('display', 'inline-block');
        $('#kindergarten_label').attr('for', 'kindergarten_'+tmp_id_district);

        var first_option = '<option value="">-- Будь ласка, оберіть --</option>';
        $('#kindergarten_class_select').html(first_option).attr('disabled', 'disabled').trigger('change');
        $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');

        id_district = tmp_id_district;
    }

    $('#id_district').trigger('change');
</script>
