<?php
    //////////////////////////////////////////////
    // надо избавиться от этой страницы, походу //
    //////////////////////////////////////////////



    $err = [];
    $first_step_breadcrumbs = '<div class="crumb current">Реквизиты</div><span>&nbsp;&rarr;&nbsp;</span><div class="crumb next">Квитанция</div><span>&nbsp;&rarr;&nbsp;</span><div class="crumb next">Оплата</div>';
    $second_step_breadcrumbs = '<div class="crumb prev">Реквизиты</div><span>&nbsp;&rarr;&nbsp;</span><div class="crumb current">Квитанция</div><span>&nbsp;&rarr;&nbsp;</span><div class="crumb next">Оплата</div>';
    $third_step_breadcrumbs = '<div class="crumb prev">Реквизиты</div><span>&nbsp;&rarr;&nbsp;</span><div class="crumb prev">Квитанция</div><span>&nbsp;&rarr;&nbsp;</span><div class="crumb current">Оплата</div>';

    $email =       (isset($_SESSION['auth']['email']))      ? $_SESSION['auth']['email'] : '';
    $first_name =  (isset($_SESSION['auth']['name']))       ? $_SESSION['auth']['name'] : '';
    $middle_name = (isset($_SESSION['auth']['fathername'])) ? $_SESSION['auth']['fathername'] : '';
    $last_name =   (isset($_SESSION['auth']['lastname']))   ? $_SESSION['auth']['lastname'] : '';

    $email =       (isset($_POST['user_email']))      ? $_POST['user_email'] : $email;
    $first_name =  (isset($_POST['user_name']))       ? $_POST['user_name'] : $first_name;
    $middle_name = (isset($_POST['user_fathername'])) ? $_POST['user_fathername'] : $middle_name;
    $last_name =   (isset($_POST['user_lastname']))   ? $_POST['user_lastname'] : $last_name;

    $isRegionStep = true;
    $breadcrumbs = $first_step_breadcrumbs;

    if(isset($_POST['paykomdebtform']) && ($_POST['paykomdebtform'] == 1))
    {
        // validate data:
        $user_lastname = trim($_POST['user_lastname']); // фамилия плательщика
        $user_name = trim($_POST['user_name']); // имя плательщика
        $user_fathername = trim($_POST['user_fathername']); // отчество плательщика
        $user_address = trim($_POST['user_address']); // адрес плательщика
        $user_email = trim($_POST['user_email']); // email плательщика
        
        if(strlen($user_lastname) == 0) $err[] = 'Фамилия плательщика не указана';
        if(strlen($user_name) == 0) $err[] = 'Имя плательщика не указано';
        if(strlen($user_address) == 0) $err[] = 'Адрес плательщика не указано';
        if(!filter_var($user_email, FILTER_VALIDATE_EMAIL)) $err[] = 'E-mail плательщика некорректный';
        
        if(count($err) == 0)
        {
            $isRegionStep = false;
            $isDetailsStep = true;
            $breadcrumbs = $second_step_breadcrumbs;

            $fio = "$user_lastname $user_name $user_fathername";
            $id_site_user = User::getUserIdByEmail($user_email);
            if($id_site_user == null)
                $id_site_user = User::registerFromPayment($user_email, $user_lastname, $user_name, $user_fathername);

            $record = $NewGai->set_request_to_ppp($error_str);
            
            if($record == false)
                $err[0] = $error_str;
            
            $record['date'] = date("d.m.Y", $record['timestamp']);
            $record['signature'] = $NewGai->get_signature($record['id']);
            $record['summ_total_upc'] = $record['summ_total'];

            // суммы в копейках, переводим в гривны:
            $record['summ_plat'] = number_format($record['summ_plat'] / 100, 2);
            $record['summ_komis'] = number_format($record['summ_komis'] / 100, 2);
            $record['summ_total'] = number_format($record['summ_total'] / 100, 2);

            $record['summ_plat'] .= (substr($record['summ_plat'], strlen($record['summ_plat']) - 2) == '.0') ? '0' : '';
            $record['summ_komis'] .= (substr($record['summ_komis'], strlen($record['summ_komis']) - 2) == '.0') ? '0' : '';
            $record['summ_total'] .= (substr($record['summ_total'], strlen($record['summ_total']) - 2) == '.0') ? '0' : '';

            $this->smarty->assign('record', $record);

            $_SESSION['KomDebt']['id'] = $record['id'];
        }
        
        if (count($err) > 0) {
            $this->smarty->assign('error', '');
            $this->smarty->assign('error_msg', $err[0]);
            $this->smarty->assign('isRegionStep', '1');
            $breadcrumbs = $first_step_breadcrumbs;
        }
    }

    $isSuccessStep = false;
    $isErrorStep = false;

    if (($_GET['step'] == 'success') || ($_GET['step'] == 'error')) {
        $breadcrumbs = $third_step_breadcrumbs;

        if (!isset($_SESSION['KomDebt'])) {
            $isErrorStep = true;
            $error_msg = 'Просмотр отплаты более недоступен';
        } else {
            $record = $NewGai->get_transaction($_SESSION['KomDebt']['id']);

            if ($record['status'] == '0') {
                // оплата ещё не завершена
            } elseif($record['status'] == '1') {
                $this->smarty->assign('isSuccessStep', '1');
            } else {
                $isErrorStep = true;
                $error_msg = UPC::get_upc_error($record['trancode']);
            }

        }
    }

?>
<h1 class="big-title">Сплата за комунальні послуги</h1>
<?php
    if ($error) {
        ?><h2 class="big-error-message"><?= $error; ?></h2><?php
        return;
    }
?>
<div class="clearr"></div>
<div class="main-content">
    <div class="breadcrumbs"><?= $breadcrumbs; ?></div>
    <?php
    
        if ($isErrorStep) {
            ?><h2 class="big-error-message"><?= $error; ?></h2><?php
        } elseif ($isSuccessStep) {
            ?>
            <form class="gerts-register" name="gerts-register" method="post" action="<?= BASE_URL . $_SERVER['REQUEST_URI']; ?>" id="register">
                <div class="success">
                    <div style="font-size: 18px;">Оплата успішно завершена <br></div>
                    <span><a style="font-size:14px;" onclick="sendInvoice(); return false;" href="#">Завантажити квитанцію про оплату в PDF &darr;</a></span>
                </div>
            </form>
            <form action="" method="post" name="send_invoice" id="send_invoice" style="display:none;">
                <input type="hidden" value="1" name="send_flag">
            </form>
            <?php
        } elseif ($isRegionStep) {
            ?>
            <form class="gerts-register" name="gerts-register" method="post" action="<?= BASE_URL . $_SERVER['REQUEST_URI']; ?>" id="register">
                <div class="error" style="<?= ($error_msg) ? '' : 'display:none;'; ?>" id="error_reg">
                    <p><span id="error_msg"><?= $error_msg; ?></span><br />
                    </p>
                </div>
                <div class="success" style="display:none;">
                    <p><span id="success_msg"></span><br /></p>
                </div>
                <div class="input">
                    <div class="row-title">Адрес</div>
                    <div class="field_col">
                        <label for="street">Улица:</label><br>
                        <input id="street" required="required" type="text" name="street" autofocus="autofocus" placeholder="Начните вводить название" value="" />
                    </div>
                    <div class="field_col">
                        <div class="select">
                            <label for="house">Дом:</label><br>
                            <select style="width:150px" name="house" disabled="disabled" id="house">
                                <option>-- выбрать --</option>
                            </select>
                        </div>
                    </div>
                    <div class="field_col">
                        <div class="select">
                            <label for="flat">Квартира:</label><br>
                            <select style="width:150px" name="flat" disabled="disabled" id="flat">
                                <option>-- выбрать --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="debtdata" style="display:none;">

                </div>

                <div class="input">
                    <div class="row-title">Данные плательщика</div>
                    <div class="field_col">
                        <label for="user_lastname">Фамилия:</label><br>
                        <input id="user_lastname" style="width:200px;" required="required" type="text" name="user_lastname" value=""/>
                    </div>
                    <div class="field_col">
                        <label for="user_name">Имя:</label><br>
                        <input id="user_name" required="required" style="width:200px;" type="text" name="user_name" value=""/>
                    </div>
                    <div class="field_col">
                        <label for="user_fathername">Отчество:</label><br>
                        <input id="user_fathername" style="width:200px;" type="text" name="user_fathername" value=""/>
                    </div>
                    <div class="field_col">
                        <label for="user_address">Адрес:</label><br>
                        <input id="user_address" title="Пример: Одесса, ул. Катериненская, 52" style="width:421px;" required="required" type="text" name="user_address" value=""/>
                    </div>
                    <div class="field_col">
                        <label for="user_email">E-Mail:</label><br>
                        <input id="user_email" title="Пример: username@example.com" required="required" style="width:200px;" type="email" name="user_email" value=""/>
                    </div>
                </div>
                <div class="clearr"></div>
                <label style="display:inline-block; text-align:left; margin:0 0 15px; font-size:12px; width:100%; cursor:text;">Нажимая кнопку «Далее» Вы соглашаетесь с правилами <a href="/policy/">публичной оферты</a></label>
                <div class="blue_button registration">
                    <input type="submit" id="submitOrder" value="Далее" style="text-transform:none;" />
                </div>
                <input type="hidden" value="1" name="paykomdebtform" />
            </form>
            <?php
        } elseif($isDetailsStep) {
            ?>
            <div class="oplata-box">
                <div class="title_row">Реквизиты</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span>Получатель</span> {*$record.dst_name*}</p>
                        <p><span>ЕГРПОУ (ОКПО)</span> {*$record.dst_okpo*}</p>
                        <p><span>МФО</span> {*$record.dst_mfo*}</p>
                        <p><span>Расчетный счет</span> {*$record.dst_rcount*}</p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="title_row">Информация о платеже</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">Дата операции</span> {*$record.date*}</p>
                        <p><span style="width:270px;">Получатель платежа</span> {*$record.dst_name*}</p>
                        <p><span style="width:270px;">Назначение платежа</span> {*$record.dest*}</p>
                        <p><span style="width:270px;">Сумма платежа</span> {*$record.summ_plat*} грн</p>
                    </div>
                    <div class="clr"></div>
                </div>

                <div class="title_row">Информация о плательщике</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">ФИО плательщика</span> {*$record.vr1*}</p>
                        <p><span style="width:270px;">Место проживания (регистрация)</span> {*$record.vr2*}</p>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="title_row">Стоимость платежа</div>
                <div class="details_row">
                    <div class="right_details">
                        <p><span style="width:270px;">Сбор за обработку платежа</span> {*$record.summ_komis*} грн</p>
                        <p><span style="width:270px;">Всего к оплате</span> {*$record.summ_total*} грн</p>
                    </div>
                    <div class="clr"></div>
                </div>

                <div style="text-align:center;">
                    <form action="https://secure.upc.ua/go/enter" method="post">
                        <input type="hidden" name="Version" value="1">
                        <input type="hidden" name="MerchantID" value="{*$UPC_MerchantID*}">
                        <input type="hidden" name="TerminalID" value="{*$UPC_TerminalID*}">
                        <input type="hidden" name="TotalAmount" value="{*$record.summ_total_upc*}">
                        <input type="hidden" name="Currency" value="980">
                        <input type="hidden" name="locale" value="ru">
                        <input type="hidden" name="SD" value="">
                        <input type="hidden" name="OrderID" value="{*$record.id*}">
                        <input type="hidden" name="PurchaseTime" value="{*$record.purchasetime*}">
                        <input type="hidden" name="PurchaseDesc" value="{*$record.name_plat*}">
                        <input type="hidden" name="Signature" value="{*$record.signature*}">
                        <input type="submit" value="Перейти к оплате">
                    </form>
                </div>
            </div>
            <?php
        }
    ?>

</div><!--End Content-->

<script type="text/javascript">
    $(document).ready(function() {
        $("#street").autocomplete({
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
                $('#flat').html('<option>-- выбрать --</option>').attr('disabled', true);
                $('#debtdata').slideUp(200);
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
                        $('#house').html(select_options).attr('disabled', false).change();
                    },
                });
            },
        });

        $("#house").change(function(){
            $.ajax({
                url: '<?= BASE_URL; ?>/ajax/json/flats',
                type: "GET",
                data: {street_id: _selected_street_id, house_id: $("#house").val()},
                dataType: "json",
                success: function(data) {
                    var select_options = '';
                    for (var i = 0; i < data.length; i++)
                        select_options += '<option value="'+ data[i].id +'">'+ data[i].label +'</option>';
                    $('#flat').html(select_options).attr('disabled', false);
                    update_komdebt_data();
                },
            });
        });

        $("#flat").change(function(){
            update_komdebt_data();
        });
    });
    
    function update_komdebt_data()
    {
        $.ajax({
            url: '<?= BASE_URL; ?>/ajax/json/komdebt',
            type: "GET",
            data: {object_id: $("#flat").val()},
            dataType: "json",
            success: function(data) {
                $('#debtdata').html('').slideDown(200);
            },
        });
    }
</script>
<style type="text/css">
    #submitOrder:disabled { cursor:default; }
    .mini_info_block .gai-fees { padding:45px 0 48px 140px; }
    label { font-size:14px; }
    .breadcrumbs { display:block; margin-bottom:25px; margin-left:30px; }
    .breadcrumbs span { display:inline-block; font-size:16px; padding:5px 0; }
    .breadcrumbs .crumb { display:inline-block; font-size:16px; padding:5px 10px; -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; }
    .breadcrumbs .crumb.current { background-color:#49addf; color:#fff; }
    .breadcrumbs .crumb.next { color:#444; }
    .breadcrumbs .crumb.prev { color:#1bc41b; }
    .field_col { display:inline-block; text-align:left; margin-right:10px; margin-bottom:10px; }
    .field_col label { float:none !important; }
    .field_col input { width:200px; }
    .field_col .select { position:relative; top:-25px; }
    .field_col .hint { display:none; }
    .input .row-title { display:block; font-weight:bold; color:#111; font-size:14px; font-family:Arial; margin:20px 0 10px; }
    form#register { padding-left:30px; }

    .details_row { padding-top:10px; padding-left:15px; }
    .details_row .right_details { float:left; }
    .clr { clear: both; }
    .title_row {background-color: #ededed; border-bottom: 1px solid #d6d6d6; border-top: 1px solid #d6d6d6; color: #21234b; font-size: 16px; height: 38px; line-height: 38px; padding-left: 17px; }
    .details_row .right_details p { line-height: 22px; padding-bottom: 10px; }
    .details_row .right_details p span {color: #707780; float: left; font-size: 12px; font-weight: bold; width: 130px; }
</style>
