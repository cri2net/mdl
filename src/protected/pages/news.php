<?php
	use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
    <content>
        <div>
            <!-- <h1>Новини</h1> -->
			<?php
			$time = time();
			$news = PDO_DB::table_list(News::TABLE, "is_actual=1 AND created_at<='$time'", "created_at DESC", 21);

			if (count($news) > 0) {
			    ?>
			    <section id="home-news">
			            <div class="row">
			            <?php
			                for ($i=0; $i < count($news); $i++) {
			                    
			                    $date = date('d ', $news[$i]['created_at']) . $MONTHS[date('n', $news[$i]['created_at'])]['ua'];
			                    if (date('Y') != date('Y', $news[$i]['created_at'])) {
			                        $date .= date(' Y', $news[$i]['created_at']);
			                    }

			                    // if (mb_strlen($news[$i]['title'], 'UTF-8') > 50) {
			                    //     $news[$i]['title'] = mb_substr($news[$i]['title'], 0, 50, 'UTF-8') . '...';
			                    // }

			                    $last = ($i == count($news) - 1);
			                    $news[$i]['image'] = PDO_DB::first(News::IMAGES_TABLE, "news_id='{$news[$i]['id']}'", "is_main DESC, pos ASC");

			                    ?>
			                    <div class="col-md-4 col-sm-6">
			                        <a href="<?= BASE_URL; ?>/news/<?= composeURLKey($news[$i]['title']); ?>_<?= $news[$i]['id']; ?>/" class="item-<?= $news[$i]['color'] ?> matchHeight">
			                            <?php
			                                if ($news[$i]['image']) {
			                                    ?>
			                                    <img src="<?= BASE_URL; ?>/photos/news/387x266fc/<?= $news[$i]['image']['id']; ?>.jpg" class="full-width" alt="">
			                                    <?php
			                                }
			                                else {
			                                	?>
			                                	<img src="<?= BASE_URL ?>/assets/images/news/nophoto-387x266.jpg" alt="cks" />
			                                	<?php 
			                                }
			                            ?>
			                            <div class="descr">
			                                <h4><?= htmlspecialchars($news[$i]['title']); ?></h4>
			                                <p><?= ($news[$i]['announce']); ?></p>
			                                <div class="info">
			                                    <span class="date"><?= $date; ?></span>
			                                    <span class="views"><?= $news[$i]['views']; ?></span>
			                                </div>
			                            </div>
			                        </a>
			                    </div>
			                    <?php
			                }
			            ?>
			            </div>
			    </section>
			    <?php
			}
			?>            
        </div>
    </content>
</div>
