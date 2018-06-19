
<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container-fluid">
    <content>
        <div class="text">
            <h1 class="big-title"><?= htmlspecialchars($static_page['h1']); ?></h1>
            <div class="main-page-text">
                <?= (trim($static_page['text'])) ? $static_page['text'] : '<b>Сторінка в стадії наповнення</b>'; ?>
            </div>
        </div>
    </content>
</div>
