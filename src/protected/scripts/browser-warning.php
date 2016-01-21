<?php
$have_header_warning = false;

if (!BROWSER_VALID) {
    ?>
    <div class="old-browsers-warning <?= ($have_header_warning) ? 'not-first-warning' : '' ?>">
        <div class="inner">
            <b>Попередження:</b> Ви використовуєте застарілу версію інтернет-браузера, що може призвести до некоректного відображення сайту.<br>
            Рекомендуємо завантажити останню версію одного з популярних браузерів
            <a class="browser firefox" target="_blank" href="https://www.mozilla.org/uk/firefox/new/">Firefox</a>
            <a class="browser chrome" target="_blank" href="https://www.google.ru/chrome/browser/desktop/">Chrome</a>
            <a class="browser opera" target="_blank" href="http://www.opera.com/uk/computer">Opera</a>
        </div>
    </div>
    <?php
    $have_header_warning = true;
}

if (Authorization::isLogin() && (!$__userData['activated'] || !$__userData['verified_email'])) {
    ?>
    <div id="verify-email-header-warning" class="old-browsers-warning <?= ($have_header_warning) ? 'not-first-warning' : '' ?>">
        <div class="inner">
            <b>Попередження:</b> Ваша електронна пошта не підтверджена. Деякий функціонал сайту може бути недоступний.
            <a class="browser" href="<?= BASE_URL; ?>/cabinet/verify-email/">Підтвердити електронну пошту</a>
        </div>
    </div>
    <?php
    $have_header_warning = true;
}

if (time() < 1453521600) {
    ?>
    <div id="verify-email-header-warning" class="old-browsers-warning <?= ($have_header_warning) ? 'not-first-warning' : '' ?>">
        <div class="inner">
            <b>Шановні відвідувачі!</b> Повідомляємо, що у зв'язку із проведенням регламентних технологічних 
робіт, пов'язаних із розширенням функціональних можливостей сервісів КП 
ГІОЦ, доступ до сайту нашого підприємства (https://www.gioc.kiev.ua)  
включно із режимом "Сплатити комунальні послуги онлайн" у період <b>із 
23:00 22 січня</b> поточного року <b>до 6:00 23 січня</b> поточного року буде 
тимчасово закритий. <br>

Дякуємо за порозуміння!
        </div>
    </div>
    <?php
    $have_header_warning = true;
}

unset($have_header_warning);
