
<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>
        <div class="text">
        <h1 class="big-title"><?= htmlspecialchars($static_page['h1']); ?></h1>
        <div class="main-page-text">
            <?= (trim($static_page['text'])) ? $static_page['text'] : '<b>Сторінка в стадії наповнення</b>'; ?>
        </div>
        <?php
            if (count($children) > 0) {
                ?>
                <div class="subtitles" > 
                <div class="page-subtitle">Підрозділи</div> 
                <?php

                foreach ($children as $child) {
                    $bg = '';
                    $class = '';
                    if ($child['icon']) {
                        $bg = BASE_URL . "/db_pic/page_icons/{$child['icon']}";
                        $bg = 'style="background-image:url(' . $bg . ');"';
                        $class = ' with-icon';
                    }
                    ?>
                    <div class="subtitle-item<?= $class; ?>" <?= $bg; ?>>
                        <a href="<?= BASE_URL . $link . $child['key'] . '/'; ?>" class="title"><?= htmlspecialchars($child['h1']); ?></a>
                        <div class="desc"><?= strip_tags ($child['announce']); ?></div>
                    </div>
                    <div class="clear"></div>
                    <?php
                }
            }
            ?>
            </div>
            <?php

            require_once(ROOT . '/protected/scripts/see_also.php');
        ?>
        </div>
    </content>
</div>
