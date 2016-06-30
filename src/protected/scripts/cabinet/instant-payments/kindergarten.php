<h1 class="big-title">Дитячі садки м.Київ (харчування)</h1>
<br><br>

<?php

    if (is_array($_SESSION['instant-payments-kinders']['columns'])) {
        foreach ($_SESSION['instant-payments-kinders']['columns'] as $key => $value) {
            $$key = $value;
        }
    }


    if (!isset($_SESSION['instant-payments-kinders']['step'])) {
        $_SESSION['instant-payments-kinders']['step'] = 'region';
    }

    try {




    } catch (Exception $e) {
        $_SESSION['instant-payments-kinders']['status'] = false;
        $_SESSION['instant-payments-kinders']['error']['text'] = $e->getMessage();
        $_SESSION['instant-payments-kinders']['step'] = 'region';
    }

    if (isset($_SESSION['instant-payments-kinders']['status']) && !$_SESSION['instant-payments-kinders']['status']) {
        ?>
        <h2 class="big-error-message">Під час надсилання повідомлення виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['instant-payments-kinders']['error']['text']; ?></div>
        <?php
        unset($_SESSION['instant-payments-kinders']['status']);
    }

    switch ($_SESSION['instant-payments-kinders']['step']) {
        case 'frame':
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

        case 'success':
            ?>
            <h2 class="big-success-message">Оплата успішно здійснена</h2>
            <?php
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
    }

?>


{*elseif $isRegionStep*}
    <form class="gerts-register" method="post" action="" id="register">
        <div class="error" style="{*$error*}" id="error_reg">
            <p><span id="error_msg">{*$error_msg*}</span><br />
            </p>
        </div>
        <div class="success" style="{*$success*}">
            <p><span id="success_msg"></span><br /></p>
        </div>
        <div class="input">
            <div class="field_col" style="height:24px; margin-top:15px;">
                <div class="select">
                    <label style="display:inline-block;  width:150px; text-align:left;" for="id_district">Район города:</label>
                    <select onchange="kinders_district_onchange(this);" style="display:inline-block; width:450px; float:none;" required="required" id="id_district" name="id_district" style="width:255px;">
                        {*foreach from=$districts item=item*}
                            <option {*if ($id_district == $item.id)*} selected="selected" {*/if*} value="{*$item.id*}">{*$item.name*}</option>
                        {*/foreach*}
                    </select>
                </div>
            </div>
        </div>
        <div class="input">
            <div class="field_col" style="height:24px; margin-top:15px;">
                <div class="select">
                    <input type="hidden" id="final_kindergarten" name="kindergarten" value="{*$R101*}" />
                    <label id="kindergarten_label" style="display:inline-block;  width:150px; text-align:left;">Учреждение:</label>
                    {*foreach from=$districts item=item*}
                        <select onchange="$('#final_kindergarten').val($(this).val()).change();" class="kindergarten_select" style="display:{*if ($id_district == $item.id)*} inline-block {*else*} none {*/if*}; width:450px; float:none;" required="required" id="kindergarten_{*$item.id*}" style="width:255px;">
                            <option value="0">-- Выберите учреждение --</option>
                            {*foreach from=$list key=list_key item=list_item*}
                                {*if ($list_item.ID_DISTRICT == $item.id)*}
                                    <option {*if ($R101 == $list_item.R101)*} selected="selected" {*/if*} value="{*$list_item.R101*}">{*$list_item.NAME_SAD*}</option>
                                {*/if*}
                            {*/foreach*}
                        </select>
                    {*/foreach*}
                </div>
            </div>
        </div>
        <div class="input">
            <div class="field_col" style="height:24px; margin-top:15px;">
                <div class="select">
                    <label style="display:inline-block;  width:150px; text-align:left;" for="kindergarten_class_select">Группа / класс:</label>
                    <select disabled="disabled" name="child_class" required="required" id="kindergarten_class_select" style="display: inline-block; width:450px; float:none;">
                        <option value="">-- Выберите группу/класс --</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="input">
            <div class="field_col" style="height:24px; margin-top:15px;">
                <div class="select">
                    <label style="display:inline-block;  width:150px; text-align:left;" for="kindergarten_fio_select"> ФИО воспитанника: </label>
                    <input disabled="disabled" type="text" name="child_fio" required="required" style="width:200px;" id="kindergarten_fio_select">
                    <span style="font-size:12px; color:#555; position:relative; top:-2px; margin-left:10px;"> введите первые три буквы фамилии</span>
                </div>
            </div>
        </div>
        <div class="input">
            <div class="field_col" style="height:24px; margin-top:15px;">
                <label style="display:inline-block;  width:150px; text-align:left;" for="kindergarten_summ">Сумма, грн:</label>
                <input id="kindergarten_summ" title="Пример: 200" style="width:450px;" required="required" type="text" name="summ" value="{*if $smarty.post.summ*}{*$smarty.post.summ*}{*/if*}"/>
            </div>
        </div>
        <div class="input">
            <div class="row-title">Данные плательщика</div>
            <div class="field_col">
                <label for="penalty_user_lastname">Фамилия:</label><br>
                <input type="text" value="{*$penalty_last_name*}" name="penalty_user_lastname" required="required" style="width:200px;" id="penalty_user_lastname">
            </div>
            <div class="field_col">
                <label for="penalty_user_name">Имя:</label><br>
                <input type="text" value="{*$penalty_first_name*}" name="penalty_user_name" style="width:200px;" required="required" id="penalty_user_name">
            </div>
            <div class="field_col">
                <label for="penalty_user_fathername">Отчество:</label><br>
                <input type="text" value="{*$penalty_middle_name*}" name="penalty_user_fathername" style="width:200px;" id="penalty_user_fathername">
            </div>
            <div class="field_col">
                <label for="penalty_user_address">Адрес:</label><br>
                <input type="text" maxlength="150" value="{*if $smarty.post.penalty_user_address*}{*$smarty.post.penalty_user_address*}{*/if*}" name="penalty_user_address" required="required" style="width:421px;" title="Пример: Одесса, ул. Катериненская, 52" id="penalty_user_address">
            </div>
            <div class="field_col">
                <label for="penalty_user_email">E-Mail:</label><br>
                <input type="email" value="{*$penalty_email*}" name="penalty_user_email" style="width:200px;" required="required" title="Пример: username@example.com" id="penalty_user_email">
            </div>
        </div>
        <div class="clearr"></div>
        <label style="display:inline-block; text-align:left; margin:0 0 15px; font-size:12px; width:100%; cursor:text;">Нажимая кнопку «Далее» Вы соглашаетесь с правилами <a target="_blank" href="/policy/">публичной оферты</a></label>

        <div class="blue_button registration btn-box">
            <button id="submitOrder" class="btn blue big bold">Далее</button>
        </div>
        <input type="hidden" value="1" name="paygairegionform" />
    </form>


<style type="text/css">
    .mini_info_block .gai-fees { padding:45px 0 48px 140px; }
    label { font-size:14px; }
    .field_col { display:inline-block; text-align:left; margin-right:10px; margin-bottom:10px; }
    .field_col label { float: none !important; }
    .field_col input { width: 200px; }
    .field_col .hint { display:none; }
    .input .row-title { display:block; font-weight:bold; color:#111; font-size:14px; font-family:Arial; margin:20px 0 10px; }

    .details_row { padding-top:10px; padding-left:15px; }
    .details_row .right_details { float:left; }
    .clr { clear: both; }
    .title_row {background-color: #ededed; border-bottom: 1px solid #d6d6d6; border-top: 1px solid #d6d6d6; color: #21234b; font-size: 16px; height: 38px; line-height: 38px; padding-left: 17px; }
    .details_row .right_details p { line-height: 22px; padding-bottom: 10px; font-size: 14px; }
    .details_row .right_details p span {color: #707780; float: left; font-weight: bold; width: 130px; }

    .check-box-line img {
        margin: 10px 10px 0 15px;
        position: relative;
        top: 6px;
    }
    .check-box-line input[type=radio] { width: 15px !important; }
    .check-box-line .text-label {
        position: relative;
        top: -5px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $('#protocol_series').poshytip({className: 'tip-twitter', showOn: 'focus', alignTo: 'target', alignX: 'inner-left', offsetX: -55, offsetY: 10, showTimeout: 100 });
        $('#protocol_number, #protocol_summ, #penalty_user_address').poshytip({className: 'tip-twitter', showOn: 'focus', alignTo: 'target', alignX: 'inner-left', offsetX: 0, offsetY: 10, showTimeout: 100 });
        $("#protocol_date").datepicker({
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat:"dd.mm.yy",
            maxDate: '0'
        });

        $("#kindergarten_fio_select").autocomplete({
            source: function(request, response){
                var val = $('#kindergarten_class_select').val();
                var Data = {};
                Data.obj = 'Kinders';
                Data.ac = 'getChildrenList';
                Data.args = [val, request.term];

                $.ajax({
                    url : './service/',
                    type: "POST",
                    dataType: "json",
                    minLength: 3,
                    data: Data,
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
            var first_option = '<option value="">-- Выберите группу/класс --</option>';
            $('#kindergarten_class_select').html(first_option).attr('disabled', 'disabled').change();
            $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');

            if (val == '') {
                return;
            }

            var Data = {};
            Data.obj = 'Kinders';
            Data.ac = 'getClassesList';
            Data.args = [val];

            jQuery.ajax({
                dataType: 'json',
                data: Data,
                type: 'POST',
                url : './service/',
                success : function(response){
                    var html = '';
                    for (var i = 0; i < response.list.length; i++) {
                        html += '<option value="'+ response.list[i].id +'">'+ response.list[i].name +'</option>';
                    };
                    $('#kindergarten_class_select').html(html).removeAttr('disabled').change();
                }
            });
        });
    });

    var id_district = {* $id_district *};
    function kinders_district_onchange(el)
    {
        var tmp_id_district = $(el).val();

        $('.kindergarten_select').hide();
        $('#kindergarten_'+tmp_id_district).css('display', 'inline-block');
        $('#kindergarten_label').attr('for', 'kindergarten_'+tmp_id_district);

        var first_option = '<option value="">-- Выберите группу/класс --</option>';
        $('#kindergarten_class_select').html(first_option).attr('disabled', 'disabled').change();
        $('#kindergarten_fio_select').val('').attr('disabled', 'disabled');

        id_district = tmp_id_district;
    }
</script>
