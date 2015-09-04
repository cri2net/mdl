<h1 class="big-title"><?= htmlspecialchars($static_page['h1']); ?></h1>
<div class="main-page-text">
    <?= $static_page['text']; ?>
</div>
<?php
    if (count($children) > 0) {
        ?> <div class="page-subtitle">Пiдроздiли</div> <?php

        foreach ($children as $child) {
            $bg = '';
            if ($child['icon']) {
                $bg = BASE_URL . "/db_pic/page_icons/{$child['icon']}";
                $bg = 'style="background-image:url(' . $bg . ');"';
            }
            ?>
            <div class="subtitle-item" <?= $bg; ?>>
                <a href="<?= BASE_URL . $link . $child['key'] . '/'; ?>" class="title"><?= htmlspecialchars($child['h1']); ?></a>
                <div class="desc"><?= $child['announce']; ?></div>
            </div>
            <div class="clear"></div>
            <?php
        }
    }

    require_once(ROOT . '/protected/scripts/see_also.php');
