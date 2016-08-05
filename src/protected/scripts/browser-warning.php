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
} elseif (Authorization::isLogin() && $__userData['broken_email']) {
    ?>
    <div id="verify-email-header-warning" class="old-browsers-warning <?= ($have_header_warning) ? 'not-first-warning' : '' ?>">
        <div class="inner">
            <b>Попередження:</b> Система не може доставити листи на Вашу електронну пошту. Деякий функціонал сайту може бути недоступний. <br>
            Ви можете <a href="<?= BASE_URL; ?>/cabinet/settings/info/">змінити</a> адресу електронної пошти, або
            <a href="<?= BASE_URL; ?>/cabinet/verify-email/">підтвердити</a> її.
        </div>
    </div>
    <?php
    $have_header_warning = true;
} elseif (Authorization::isLogin() && ($__userData['name'] == '')) {
    ?>
    <div id="verify-email-header-warning" class="old-browsers-warning <?= ($have_header_warning) ? 'not-first-warning' : '' ?>">
        <div class="inner">
            <b>Попередження:</b> Ваш профіль заповнено не повністю.
            Будь ласка, вкажіть своє ім’я на <a href="<?= BASE_URL; ?>/cabinet/settings/info/">сторінці налаштувать</a>
        </div>
    </div>
    <?php
    $have_header_warning = true;
}

unset($have_header_warning);
