<?php require_once(ROOT . '/protected/scripts/slider.php'); ?>
<h1 class="big-title green">Новини</h1>
<?php
    $items_on_page = 10;

    if (isset($__route_result['values']['page'])) {
        $currentPage = abs((int)$__route_result['values']['page']);
    }
    else {
        $currentPage = 1;
    }

    $stm = PDO_DB::query("SELECT COUNT(*) FROM ". News::TABLE ." WHERE is_actual=1");
    $pagesCount = (int)ceil($stm->fetchColumn() / $items_on_page);
    $from = ($currentPage * $items_on_page) - $items_on_page;
    $currentPage = min($currentPage, $pagesCount);
    $url_for_paging = BASE_URL . '/news/';

    $news = PDO_DB::table_list(News::TABLE, "`is_actual`=1", "created_at DESC", "$from, $items_on_page");

    if (count($news) > 0) {
        ?>
        <div class="news-list">
            <?php
                for ($i=0; $i < count($news); $i++) {
                    if (($i > 0) && ($i % 2 == 0)) {
                        ?></div><div class="news-list"><?php
                    }
                    
                    $date = date('d ', $news[$i]['created_at']) . $MONTHS[date('n', $news[$i]['created_at'])]['ua'];
                    if (date('Y') != date('Y', $news[$i]['created_at'])) {
                        $date .= date(' Y', $news[$i]['created_at']);
                    }

                    if (mb_strlen($news[$i]['title'], 'UTF-8') > 50) {
                        $news[$i]['title'] = mb_substr($news[$i]['title'], 0, 50, 'UTF-8') . '...';
                    }

                    if (!$news[$i]['announce']) {
                        $news[$i]['announce'] = strip_tags($news[$i]['text']);
                        if (mb_strlen($news[$i]['announce'], 'UTF-8') > 200) {
                            $news[$i]['announce'] = mb_substr($news[$i]['announce'], 0, 200, 'UTF-8') . '...';
                        }
                    }

                    ?>
                    <div class="news-item <?= ($i % 2 == 0) ? 'first' : ''; ?>">
                        <h2 class="title"><?= htmlspecialchars($news[$i]['title']); ?></h2>
                        <div class="date"><?= $date; ?></div>
                        <div class="announce"><?= ($news[$i]['announce']); ?></div>
                        <div class="details"><a href="<?= BASE_URL; ?>/news/<?= composeURLKey($news[$i]['title']); ?>_<?= $news[$i]['id']; ?>/">детальнiше...</a></div>
                    </div>
                    <?php
                }
            ?>
        </div>
        <div class="clear"></div>
        <?php
            if ($pagesCount > 1) {
                ?>
                <div class="align-center">
                    <div class="btn-more">
                        <div class="btn green bold"><div class="icon-reload"></div>Показати ще</div>
                    </div>
                    <div class="ruler">
                        <?php
                            insertPagination($pagesCount, $currentPage - 1, $url_for_paging, $items_on_page);
                        ?>
                    </div>
                </div>
                <?php
            }
    }
