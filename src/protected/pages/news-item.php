<h2><?= htmlspecialchars($__news_item['title']); ?></h2>
<div class="main-page-text">
	<?= $__news_item['text']; ?>
    <?php
        $source = StaticPage::getAnyLinks($__news_item['id'], 'news', 'source');
        if (count($source) > 0) {
            // по идее, источников может быть несколько. Но мы отображаем только первый
            $source = $source[0];
            $source['title'] = ($source['title']) ? $source['title'] : $source['link'];
            ?>
            <div>
                <i>Джерело: <a target="_blank" href="<?= htmlspecialchars($source['link'], ENT_QUOTES); ?>"><?= htmlspecialchars($source['title']); ?></a></i>
            </div>
            <?php
        }
    ?>
</div>
<?php
    $see_also = StaticPage::getSeeAlso($__news_item['id'], 'news');
    require_once(ROOT . '/protected/scripts/see_also.php');
