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

    $pages = array();
    
    if ($pagesCount > 10) {
        if ($currentPage > 4) {
            $pages = array(0, 1, 'points');
            $start_page = ($pagesCount - $currentPage >= 3)?$currentPage-1:$pagesCount-5;
            
            for ($i = $start_page; $i < $start_page + 3; $i++) {
                $pages[] = $i;
            }
            
            $last_page = $pages[count($pages) - 1];
            
            if ($last_page < $pagesCount - 3) {
                $pages[] = 'points';
            }
            
            for ($i=$pagesCount-2; $i<$pagesCount; $i++) {
                if ($i > $last_page) {
                    $pages[] = $i;
                }
            }
        } else {
            for ($i = 0; $i < $currentPage + 2; $i++) {
                $pages[] = $i;
            }
            $pages[] = 'points';
            for($i = $pagesCount - 2; $i < $pagesCount; $i++) {
                $pages[] = $i;
            }
        }
    } else {
        for ($i=0; $i<$pagesCount; $i++) {
            $pages[] = $i;
        }
    }


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

        $currentPage--;

        if ($pagesCount > 1) {
            ?>
            <div class="align-center">
                <div class="btn-more">
                    <div class="btn green bold"><div class="icon-reload"></div>Показати ще</div>
                </div>
                <div class="ruler">
                    <a href="<?= $url_for_paging; ?>" class="first"></a>
                    <?php
                        $url_for_prev_page = ($currentPage == 0)
                            ? '#" onclick="return false;"'
                            : $url_for_paging . ($currentPage - 1) . '/';

                        if ($currentPage - 1 == 0) {
                            $url_for_prev_page = $url_for_paging;
                        }

                        $url_for_next_page = ($currentPage == $pagesCount - 1)
                            ? '#" onclick="return false;"'
                            : $url_for_paging . ($currentPage + 1) . '/';
                    ?>
                    <a href="<?= $url_for_prev_page; ?>" class="prev"></a>
                    <?php
                        for ($i=0; $i<count($pages); $i++) {
                            if (($pages[$i] !== 'points') && ($currentPage == $pages[$i])) {
                                ?><a class="current" href="<?= $url_for_prev_page; ?>"><?= $pages[$i] + 1; ?></a><?php
                            } elseif ($pages[$i] === 0) {
                                ?><a href="<?= $url_for_paging; ?>">1</a><?php
                            } elseif ($pages[$i] !== 'points') {
                                ?><a href="<?= $url_for_paging; ?><?= $pages[$i] + 1; ?>/"><?= $pages[$i] + 1; ?></a><?php
                            } else {
                                ?><a onclick="return false;" href="#">...</a><?php
                            }
                        }
                    ?>
                    <a href="<?= $url_for_next_page; ?>" class="next"></a>
                    <a href="<?= $url_for_paging; ?><?= $pagesCount; ?>/" class="last"></a>
                </div>
            </div>
            <?php
        }
    }
