<?php
    if (isset($_SESSION['contacts']['status']) && $_SESSION['contacts']['status']) {
        ?><h2 class="big-success-message">Ваше повідомлення отримано. Дякуємо за звернення</h2> <?php
        unset($_SESSION['contacts']);
    } elseif (isset($_SESSION['contacts']['status'])) {
        ?>
        <h2 class="big-error-message">При надсиланні повідомлення виникли помилки:</h2>
        <div class="error-desription"><?= $_SESSION['contacts']['error']['text']; ?></div>
        <?php
        unset($_SESSION['contacts']['status']);
    }

    $_contacts_name  = (isset($_SESSION['contacts']['name']))  ? $_SESSION['contacts']['name'] : '';
    $_contacts_text  = (isset($_SESSION['contacts']['text']))  ? $_SESSION['contacts']['text'] : '';
    $_contacts_email = (isset($_SESSION['contacts']['email'])) ? $_SESSION['contacts']['email'] : '';

    $_contacts_name = htmlspecialchars($_contacts_name, ENT_QUOTES);
    $_contacts_text = htmlspecialchars($_contacts_text, ENT_QUOTES);
    $_contacts_email = htmlspecialchars($_contacts_email, ENT_QUOTES);
?>
<h1 class="big-title normal-margin">Контакти</h1>
<div class="page-map">
    <div class="item clock"><a class="dotted" href="#page-map-clock">Графiк роботи</a></div>
    <div class="item phone"><a class="dotted" href="#page-map-phone">Телефони</a></div>
    <div class="item marker"><a class="dotted" href="#page-map-marker">Адреса</a></div>
    <div class="item letter"><a class="dotted" href="#page-map-letter">Зворотній звязок</a></div>
</div>
<h3 id="page-map-clock" class="page-subtitle border-top">Графiк роботи</h3>

<div class="work-content">
    <div class="line green">
        <div class="col">Понеділок—Четвер</div>
        <div class="col-r">8<sup>30</sup>—17<sup>30</sup></div>
    </div>
    <div class="line green">
        <div class="col">П'ятниця</div>
        <div class="col-r">8<sup>30</sup>—16<sup>15</sup></div>
    </div>
    <div class="line yellow">
        <div class="col">Обідня перерва</div>
        <div class="col-r">12<sup>30</sup>—13<sup>15</sup></div>
    </div>
    
    <h4 class="title">Прийом "Відділом Звернень"</h4>
    <div class="line green">
        <div class="col">Понеділок—Четвер</div>
        <div class="col-r">9<sup>00</sup>—17<sup>00</sup></div>
    </div>
    <div class="line green">
        <div class="col">П'ятниця</div>
        <div class="col-r">9<sup>00</sup>—16<sup>00</sup></div>
    </div>
   
    <h4 class="title">Прийом керівництвом</h4>
    <div class="line green">
        <div class="col">Вівторок</div>
        <div class="col-r">14<sup>00</sup>—17<sup>00</sup></div>
    </div>
    <div class="line comment">
        за попереднім записом в секретаріаті: тел. +380 (44) 238-80-05
    </div>
</div>

<h3 id="page-map-phone" class="page-subtitle border-top">Телефони та електрона пошта</h3>
<h4 class="title" style="margin-top:43px;">Контакт-центр</h4>
<div style="phone-line">
    <span style="width:49px; display:inline-block; line-height:24px;">тел.:</span>
    +38 (044) 238 80 25,
    +38 (044) 238 80 27
</div>
<h4 class="title">Приймальня</h4>
<div style="phone-line">
    <span style="width:49px; display:inline-block; line-height:24px;">тел.:</span>
    +38 (044) 513-52-52,
    +38 (044) 238-80-55,
    +38 (044) 238-80-05
</div>
<div style="phone-line">
    <span style="width:49px; display:inline-block; line-height:24px;">факс:</span>
    +38 (044) 238-80-38,
    +38 (044) 238-80-50
</div>
<h4 class="title">Електронна пошта</h4>
<div style="phone-line">
    <a class="no-decoration" href="mailto:secretary@gioc-kmda.kiev.ua" target="_blank">secretary@gioc-kmda.kiev.ua</a>
</div>


<h3 id="page-map-marker" class="page-subtitle border-top">Адреса</h3>
<h4 class="title">Поштова адреса</h4>
02192, Україна, м. Київ, вул. Космічна, 12-а
<div class="map-block" style="height:274px; width: 100%;">
    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d634.9232534968156!2d30.622652!3d50.465441!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0000000000000000%3A0x37f7d3eaa6d2faa4!2z0JrQvtC80YPQvdCw0LvRjNC90LUg0L_RltC00L_RgNC40ZTQvNGB0YLQstC-ICLQk9C-0LvQvtCy0L3QuNC5INGW0L3RhNC-0YDQvNCw0YbRltC50L3QviAtINC-0LHRh9C40YHQu9GO0LLQsNC70YzQvdC40Lkg0YbQtdC90YLRgCI!5e0!3m2!1sru!2sua!4v1440763298928" width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>
</div>


<h3 id="page-map-letter" class="page-subtitle border-top">Зворотнiй зв’язок</h3>
<form class="feedback-form" action="<?= BASE_URL; ?>/post/contacts/" method="post">
    <input type="text" name="country" value="" style="display:none;">
    <div class="field-group">
        <label>
            Iм’я <span class="star-required" title="Обов'язкове поле">*</span> <br>
            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= $_contacts_name; ?>" type="text" name="name" class="txt" required="required">
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
    </div>
    <div class="field-group">
        <label>
            Електрона пошта <span class="star-required" title="Обов'язкове поле">*</span> <br>
            <input onblur="registration_ckeck_empty_fileld(this);" value="<?= $_contacts_email; ?>" type="email" name="email" class="txt" required="required">
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
    </div>
    <div class="field-group">
        <label>
            Текст повiдомлення <span class="star-required" title="Обов'язкове поле">*</span> <br>
            <textarea onblur="registration_ckeck_empty_fileld(this);" required="required" name="text" class="txt"><?= $_contacts_text; ?></textarea>
        </label>
        <div style="display:none;" class="error-text"><div class="error-icon"></div> поле не повинно бути порожнiм</div>
    </div>
    <div class="field-group">
        <button class="btn green bold">Надiслати</button>
    </div>
</form>