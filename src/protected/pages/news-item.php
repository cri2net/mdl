<?php
    use cri2net\php_pdo_db\PDO_DB;
?>

<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>

        <div class="text">
            <div class="pull-right info-block">
                <span class="date"><?= date('j', $__news_item['created_at']) ?> <?= $MONTHS[date('n', $__news_item['created_at'])]['ua'] ?> <?= date('Y', $__news_item['created_at']) ?></span>
                <?php
                    $url_for_share = BASE_URL . '/news/' . composeURLKey($__news_item['title']) . "_{$__news_item['id']}/";
                    $title_for_share = $__news_item['title'];
                    require(PROTECTED_DIR . '/scripts/share.php');
                ?>
            </div>

            <h1><?= htmlspecialchars($__news_item['title']); ?></h1>

            <div class="text-inner">
                <?php
                    $photos = PDO_DB::table_list(News::IMAGES_TABLE, "news_id='{$__news_item['id']}'", "is_main DESC, pos ASC");

                    if (count($photos) > 0) {
                        ?>
                        <div class="news-slider swiper-container">
                            <div class="swiper-wrapper">
                            <?php
                                foreach($photos as $p) {
                                    ?>
                                    <div class="swiper-slide">
                                        <img src="<?= BASE_URL ?>/photos/news/1101x620fc/<?= $p['id'] ?>.jpg" alt="Новость" class="full-width">
                                        <label>
                                            &nbsp;
                                        </label>
                                    </div>
                                    <?php
                                }
                            ?>
                            </div>
                            </div>
                            <div class="swiper-pagination">
                                <a class="arrow-left"></a>
                                <a class="arrow-right"></a>
                            </div>
                        </div>
                        <?php
                    }
                ?>

                <?= $__news_item['text'] ?>
            </div>
        </div>
    </content>
</div>

