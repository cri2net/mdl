<?php
    // ещё раз берём последний элемент (текущий) из массива, чтоб не привязываться к временной переменной из другого файла
    $static_page = $__static_pages_array[count($__static_pages_array) - 1];
    $children = StaticPage::getChildren($static_page['id']);
    $link = StaticPage::getPath($static_page['id']);
?>
<h1 class="big-title"><?= htmlspecialchars($static_page['h1']); ?></h1>
<div class="main-page-text">
    <?= $static_page['text']; ?>
</div>
<?php
    if (count($children) > 0) {
        ?> <div class="page-subtitle">Пiдроздiли</div> <?php

        foreach ($children as $child) {
            ?>
            <div class="subtitle-item <?= $child['key']; ?>">
                <a href="<?= BASE_URL . $link . $child['key'] . '/'; ?>" class="title"><?= htmlspecialchars($child['h1']); ?></a>
                <div class="desc"><?= $child['announce']; ?></div>
            </div>
            <div class="clear"></div>
            <?php            
        }
    }
?>