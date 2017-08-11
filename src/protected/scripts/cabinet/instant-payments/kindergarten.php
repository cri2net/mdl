<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>
<div class="container">
    <content>
        <div class="portlet">
<!--         <h1 class="big-title">Дитячі садки (харчування)</h1>
        <br><br> -->

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
                
                    $file = PROTECTED_DIR . "/conf/payments/tas/tas";
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
                    <form id="kindergarten-form" class="feedback-form" method="post" action="<?= BASE_URL; ?>/post/cabinet/instant-payments/kindergarten/">
                        <div id="tmp-error-description" style="display: none;" class="alert alert-warning">Почніть вводити прізвище учня та обов’язково виберіть учня зі списку</div>
                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Місто <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <select onchange="kinders_city_onchange(this);" class="form-txt" required="required" id="city_id" name="city_id">
                                        <option value="<?= Street::KIEV_ID; ?>">Київ</option>
                                        <option value="<?= Street::ODESSA_ID; ?>">Одеса</option>
                                    </select>
                                </label>
                            </div>
                        </div>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    РОАМ <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <select onchange="kinders_district_onchange(this);" class="form-txt" required="required" id="id_district" name="id_district" onblur="registration_ckeck_empty_fileld(this);">
                                        <option value="">-- Будь ласка, оберіть --</option>
                                    </select>
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Установа <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <select class="txt kindergarten_select" id="final_kindergarten" name="kindergarten" required="required">
                                        <option value="">-- Будь ласка, оберіть --</option>
                                    </select>
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Група / клас <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <select class="form-txt" required="required" id="kindergarten_class_select" name="child_class" onblur="registration_ckeck_empty_fileld(this);">
                                        <option value="">-- Будь ласка, оберіть --</option>
                                    </select>
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div title="введіть перші три літери прізвища" class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    ПІБ учня <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" class="form-txt"  disabled="disabled" type="text" name="child_fio" required="required" style="width:200px;" id="kindergarten_fio_select">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Сума, грн <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($summ, ENT_QUOTES); ?>" type="text" name="summ" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <h3>Платник</h3>

                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    Прізвище <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_lastname, ENT_QUOTES); ?>" type="text" name="penalty_user_lastname" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>

                            <div class="form-group" style="display: inline-block;">
                                <label>
                                    Ім’я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_name, ENT_QUOTES); ?>" type="text" name="penalty_user_name" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>
                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group" style="display: inline-block; float: left; margin-right: 61px;">
                                <label>
                                    По-батькові <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_fathername, ENT_QUOTES); ?>" type="text" name="penalty_user_fathername" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                            <?php
                                $display_email = (isset($__userData['email']) && filter_var($penalty_user_email, FILTER_VALIDATE_EMAIL))
                                    ? 'display: none;'
                                    : 'display: inline-block;';
                            ?>
                            <div class="form-group" style="<?= $display_email; ?>">
                                <label>
                                    Електронна пошта <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_email, ENT_QUOTES); ?>" type="email" name="penalty_user_email" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>
                        
                        <div style="display: inline-block; float: none; width: 100%;">
                            <div class="form-group full-width">
                                <label>
                                    Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                                    <input style="width: 655px;" onblur="registration_ckeck_empty_fileld(this);" value="<?= htmlspecialchars($penalty_user_address, ENT_QUOTES); ?>" type="text" name="penalty_user_address" class="form-txt" required="required">
                                </label>
                                <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
                            </div>
                        </div>

                        <div class="form-group align-center">
                            <button id="submitOrder" class="btn btn-blue btn-md">Далі</button>
                        </div>
                    </form>
                    <script type="text/javascript">
                        $(function(){
                            $('#city_id').trigger('change');
                        });
                    </script>
                    <?php
            }

        ?>
        </div>
    </content>
</div>

<script type="text/javascript">
    $(function(){

        kinder_selected = false;

        $("#kindergarten_fio_select").autocomplete({
            source: function(request, response){

                var val = $('#kindergarten_class_select').val();
                kinder_selected = false;

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
            },
            select: function (a, b) {
                kinder_selected = true;
            }
        });

        $('#kindergarten_class_select').change(function() {
            var val = $('#kindergarten_class_select').val();
            $('#kindergarten_fio_select').val('');

            if (val == '') {
                $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');
                kinder_selected = false;
                return;
            }

            $('#kindergarten_fio_select').removeAttr('disabled');
        });

        $('#kindergarten-form').submit(function() {
            if (kinder_selected) {
                $('#tmp-error-description').hide(400);
            } else {
                $('#tmp-error-description').show(400);
            }
            return kinder_selected;
        });

        $('#final_kindergarten').change(function() {
            kinder_selected = false;
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

    function kinders_district_onchange(el)
    {
        kinder_selected = false;
        $.ajax({
            dataType: 'json',
            data: {
                action: 'get_institution_list',
                id_district: $(el).val()
            },
            type: 'POST',
            url : BASE_URL + '/ajax/json/kindergarten',
            success : function(response) {
                var html = '';
                for (var i = 0; i < response.list.length; i++) {
                    html += '<option value="'+ response.list[i].id +'">'+ response.list[i].name +'</option>';
                };
                $('#final_kindergarten').html(html).removeAttr('disabled').trigger('change');
            }
        });

        var first_option = '<option value="">-- Выберите группу/класс --</option>';
        $('#kindergarten_class_select').html(first_option).attr('disabled', 'disabled').trigger('change');
        $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');
    }

    function kinders_city_onchange(el)
    {
        kinder_selected = false;
        $.ajax({
            dataType: 'json',
            data: {
                action: 'get_district_list',
                city_id: $(el).val()
            },
            type: 'POST',
            url : BASE_URL + '/ajax/json/kindergarten',
            success : function(response) {
                var html = '';
                for (var i = 0; i < response.list.length; i++) {
                    html += '<option value="'+ response.list[i].id +'">'+ response.list[i].name +'</option>';
                };
                $('#id_district').html(html).removeAttr('disabled').trigger('change');
            }
        });
    }
</script>
