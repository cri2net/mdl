<?php
    if (isset($_SESSION['contacts']['status']) && $_SESSION['contacts']['status']) {
        ?><h2 class="big-success-message">Ваше повідомлення отримано. Дякуємо за звернення</h2> <?php
        unset($_SESSION['contacts']);
    } elseif (isset($_SESSION['contacts']['status'])) {
        ?>
        <h2 class="big-error-message">Під час надсилання повідомлення виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['contacts']['error']['text']; ?></div>
        <?php
        unset($_SESSION['contacts']['status']);
    }

    $_contacts_name    = (isset($_SESSION['contacts']['name']))    ? $_SESSION['contacts']['name']    : '';
    $_contacts_text    = (isset($_SESSION['contacts']['text']))    ? $_SESSION['contacts']['text']    : '';
    $_contacts_email   = (isset($_SESSION['contacts']['email']))   ? $_SESSION['contacts']['email']   : '';
    $_contacts_address = (isset($_SESSION['contacts']['address'])) ? $_SESSION['contacts']['address'] : '';
    $_contacts_subject = (isset($_SESSION['contacts']['subject'])) ? $_SESSION['contacts']['subject'] : '';

    $_contacts_name = htmlspecialchars($_contacts_name, ENT_QUOTES);
    $_contacts_text = htmlspecialchars($_contacts_text, ENT_QUOTES);
    $_contacts_email = htmlspecialchars($_contacts_email, ENT_QUOTES);
?>
<h1 class="big-title normal-margin">Контакти</h1>
<div class="page-map">
    <div class="item clock"><a class="dotted" href="#page-map-clock">Графік роботи</a></div>
    <div class="item phone"><a class="dotted" href="#page-map-phone">Телефони</a></div>
    <div class="item marker"><a class="dotted" href="#page-map-marker">Адреса</a></div>
    <div class="item letter"><a class="dotted" href="#page-map-letter">Зворотній зв’язок</a></div>
</div>
<?php
    // может странный способ, но это один запрос к БД, а не 3
    $list = PDO_DB::table_list(TABLE_PREFIX . 'text', "variable IN ('CONTACTS_BLOCK_CLOCK', 'CONTACTS_BLOCK_PHONE', 'CONTACTS_BLOCK_MARKER')");
    for ($i=0; $i < count($list); $i++) { 
        $_list[$list[$i]['variable']] = $list[$i]['text'];
    }

    echo $_list['CONTACTS_BLOCK_CLOCK'], $_list['CONTACTS_BLOCK_PHONE'], $_list['CONTACTS_BLOCK_MARKER'];
?>
<h3 id="page-map-letter" class="page-subtitle border-top">Зворотній зв’язок</h3>
<form class="feedback-form" action="<?= BASE_URL; ?>/post/contacts/" method="post">
    <input type="text" name="country" value="" style="display:none;" autocomplete="off">
    <div style="display: inline-block; float: none; width: 100%;">
        <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
            <label>
                Ім’я <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <input onblur="registration_ckeck_empty_fileld(this);" value="<?= $_contacts_name; ?>" type="text" name="name" class="txt" required="required">
            </label>
            <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
        <div class="field-group" style="display: inline-block;">
            <label>
                Адреса <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <input onblur="registration_ckeck_empty_fileld(this);" value="<?= $_contacts_address; ?>" type="text" name="address" class="txt" required="required">
            </label>
            <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
    </div>
    <div style="display: inline-block; float: none; width: 100%;">
        <div class="field-group" style="display: inline-block; float: left; margin-right: 61px;">
            <label>
                Електронна пошта <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <input onblur="registration_ckeck_empty_fileld(this);" value="<?= $_contacts_email; ?>" type="email" name="email" class="txt" required="required">
            </label>
            <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
        <div class="field-group" style="display: inline-block;">
            <label>
                Тема звернення <span class="star-required" title="Обов’язкове поле">*</span> <br>
                <input onblur="registration_ckeck_empty_fileld(this);" value="<?= $_contacts_subject; ?>" type="text" name="subject" class="txt" required="required">
            </label>
            <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
        </div>
    </div>

    <div class="field-group">
        <label>
            Текст повідомлення <span class="star-required" title="Обов’язкове поле">*</span> <br>
            <textarea onblur="registration_ckeck_empty_fileld(this);" required="required" name="text" class="txt"><?= $_contacts_text; ?></textarea>
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнім</div>
    </div>
    <div class="field-group">
        <button class="btn green bold">Надіслати</button>
    </div>
</form>
