<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    $current_section = $__route_result['values']['section'];
    $__userData = User::getUserById(Authorization::getLoggedUserId());
?>
<div class="h1-line-cabinet">
    <h1 class="big-title">Налаштування профілю</h1>
    <div class="secure">особистий кабiнет</div>
</div>
<div class="cabinet-settings">
    <div class="page-tabs">
        <?php
            $sections = [
                'info' => 'Персональні дані',
                'notifications' => 'Налаштування повідомлень',
                'rule' => 'Управління профілем',
            ];
            $i = 0;
            
            foreach ($sections as $key => $value) {
                $i++;
                $current = ($current_section == $key);
                $class = 'tab';
                $class .= ($current) ? ' current' : '';
                $class .= ($i == count($sections)) ? ' last' : '';

                if ($current) {
                    ?><div class="<?= $class; ?>"><?= $value; ?></div><?php
                } else {
                    ?><a class="<?= $class; ?>" href="<?= BASE_URL; ?>/cabinet/settings/<?= $key; ?>/"><?= $value; ?></a><?php
                }
            }
        ?>
    </div>
    
    <?php
        if (isset($_SESSION['cabinet-settings']['status']) && !$_SESSION['cabinet-settings']['status']) {
            ?>
            <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
            <div class="error-description"><?= $_SESSION['cabinet-settings']['error']['text']; ?></div>
            <?php
            unset($_SESSION['cabinet-settings']['status']);
        } elseif (isset($_SESSION['cabinet-settings']['status'])) {
            ?><h2 class="big-success-message"><?= $_SESSION['cabinet-settings']['text']; ?></h2> <?php
            unset($_SESSION['cabinet-settings']);
        }
    ?>

    <form onsubmit="registration_form_submit();" class="form-cabinet-settings form-cabinet-settings-<?= $current_section; ?>" method="post" action="<?= BASE_URL; ?>/post/cabinet/settings/<?= $current_section; ?>/">
        <?php
            $file = ROOT . "/protected/scripts/cabinet/settings/$current_section.php";
            if (file_exists($file)) {
                require_once($file);
            }
        ?>
    </form>
</div>
