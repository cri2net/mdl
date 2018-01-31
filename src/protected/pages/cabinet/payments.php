<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
    }

    define('NAVBAR_FOR_PAYMENTS', true);
    $current_section = $__route_result['values']['section'];
?>
<body>
<?php
//    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container-fluid">
    <content>
        <div class="cabinet-settings cabinet-payments">
            <?php

                require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');

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

                $file = PROTECTED_DIR . "/scripts/cabinet/payments/$current_section.php";
                if (file_exists($file)) {
                    require_once($file);
                }
            ?>
        </div>
    </content>
</div>
