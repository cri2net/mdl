<?php
if (count($see_also) > 0) {
    ?> <div class="page-subtitle">Дивіться також</div> <?php

    foreach ($see_also as $see_also_item) {
        
        switch ($see_also_item['page_type']) {
            
            case 'static_page':
                $url = BASE_URL . StaticPage::getPath($see_also_item['page_id']);
                $item = StaticPage::getById($see_also_item['page_id']);
                $title = $item['h1'];
                break;
        }

        $bg = '';
        if ($item['icon']) {
            $bg = BASE_URL . "/db_pic/page_icons/{$item['icon']}";
            $bg = 'style="background-image:url(' . $bg . ');"';
        }
        ?>
        <div class="subtitle-item" <?= $bg; ?>>
            <a href="<?= $url; ?>" class="title"><?= htmlspecialchars($title); ?></a>
            <div class="desc"><?= $see_also_item['announce']; ?></div>
        </div>
        <div class="clear"></div>
        <?php
    }
}
