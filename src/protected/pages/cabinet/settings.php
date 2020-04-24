<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
    }
    define('NAVBAR_FOR_SETTINGS', true);
    $current_section = $__route_result['values']['section'];
?>
<div id="all_body_container">
<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container-fluid">
<content>
<div class="cabinet-settings">
    <?php
        if (isset($_SESSION['cabinet-settings']['status']) && !$_SESSION['cabinet-settings']['status']) {
            ?>
            <div class="alert alert-danger">
                <?= $_SESSION['cabinet-settings']['error']['text']; ?>
            </div>
            <?php
            unset($_SESSION['cabinet-settings']['status']);
        } elseif (isset($_SESSION['cabinet-settings']['status'])) {
            ?>
            <div class="alert alert-success">
                <?= $_SESSION['cabinet-settings']['text']; ?>
            </div>
            <?php
            unset($_SESSION['cabinet-settings']);
        }
    ?>

    <form onsubmit="registration_form_submit();" class="form-cabinet-settings form-cabinet-settings-<?= $current_section; ?>" method="post" action="<?= BASE_URL; ?>/post/cabinet/settings/<?= $current_section; ?>/">
        <?php
            $file = PROTECTED_DIR . "/scripts/cabinet/settings/$current_section.php";
            if (file_exists($file)) {
                require_once($file);
            }
        ?>
    </form>
</div>
</content>
</div>