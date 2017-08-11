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
                <div class="social">
                    <a href="#" class="social-vk"></a>
                    <a href="#" class="social-fb"></a>
                    <a href="#" class="social-twitter"></a>
                </div>
            </div>

            <h1><?= $__news_item['title'] ?></h1>

            <div class="text-inner">
                <?php
                	$photos = PDO_DB::table_list(News::IMAGES_TABLE, "news_id='{$__news_item['id']}'", "is_main DESC, pos ASC");

                	if(count($photos) > 0) {
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
	                        <a href="#" class="arrow-left"></a>
	                        <a href="#" class="arrow-right"></a>
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

