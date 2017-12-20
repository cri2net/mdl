<?php
if (count($children) > 0) {
    ?>
    <div class="subtitles">
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
    ?>
    </div>
    <?php
}
