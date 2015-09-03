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

    if (count($see_also) > 0) {
        ?> <div class="page-subtitle">Дивiться також</div> <?php

        foreach ($see_also as $see_also_item) {
            
            switch ($see_also_item['page_type']) {
                
                case 'static_page':
                    $url = BASE_URL . StaticPage::getPath($see_also_item['page_id']);
                    $item = StaticPage::getById($see_also_item['page_id']);
                    $title = $item['h1'];
                    break;

                case 'news':
                    $url = News::getNewsURL($see_also_item['page_id']);
                    $item = PDO_DB::row_by_id(News::TABLE, $see_also_item['page_id']);
                    $title = $item['title'];
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
?>