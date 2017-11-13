<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>
        <div class="portlet">
            <h2>Питання до фахівця</h2><br/>
            <?php
                if (isset($_SESSION['services_request']['status']) && $_SESSION['services_request']['status']) {
                    require_once(PROTECTED_DIR . '/pages/services/request/result.php');
                    unset($_SESSION['services_request']);
                } elseif (isset($_SESSION['services_request']['status'])) {
                    ?>
                    <div class="alert alert-warning">
                        <?= $_SESSION['services_request']['error']['text']; ?>
                    </div>
                    <?php
                    unset($_SESSION['services_request']);
                } else {
                    require_once(PROTECTED_DIR . '/pages/services/request/form.php');
                }
            ?>
        </div>
    </content>
</div>
