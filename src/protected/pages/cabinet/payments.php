<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    $current_section = $__route_result['values']['section'];
?>
<div class="h1-line-cabinet">
    <h1 class="big-title">Мої платежі</h1>
    <div class="secure">особистий кабінет</div>
</div>
<div class="cabinet-settings cabinet-payments">
    <?php
        if ($current_section != 'details') {
            ?>
            <div class="page-tabs">
                <?php
                    $sections = [
                        'history' => 'Історія платежів',
                        'komdebt' => 'ЖКГ платежі',
                        'instant' => 'Миттєві платежі',
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
                            ?><a class="<?= $class; ?>" href="<?= BASE_URL; ?>/cabinet/payments/<?= $key; ?>/"><?= $value; ?></a><?php
                        }
                    }
                ?>
            </div>
            <?php
        }
    ?>
    
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

        $file = ROOT . "/protected/scripts/cabinet/payments/$current_section.php";
        if (file_exists($file)) {
            require_once($file);
        }
    ?>
</div>
