<body>
<?php
    $static_page = StaticPage::getByURI(null, $__static_pages_array);
    $__route_result['controller'] = 'static_page';
    $__route_result['action'] = 'index';
    
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container">
    <content>
        <div class="text">
            <h1 class="big-title"><?= htmlspecialchars($static_page['h1']); ?></h1>
            <div class="main-page-text">
                <?= $static_page['text']; ?>
            </div>
        </div>
        <?php
            require_once(PROTECTED_DIR . '/scripts/map-form.php');
        ?>
    </content>
</div>
