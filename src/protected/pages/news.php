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
        echo News::fetchList($news);
        ?>
        <div id="mews-insert-before"></div>
        <div class="clear"></div>
        <?php
            if ($pagesCount > 1) {
                ?>
                <script type="text/javascript">
                    var news_on_page = <?= $items_on_page; ?>;
                    var news_current_page = <?= $currentPage; ?>;
                    var news_pages_cont = <?= $pagesCount; ?>;
                </script>
                <div class="align-center">
                    <?php
                        if ($currentPage < $pagesCount) {
                            ?>
                            <div id="btn-more-block" class="btn-more">
                                <div onclick="show_more_news('icon-reload-news');" class="btn green bold"><img id="icon-reload-news" class="icon-reload" src="<?= BASE_URL; ?>/pic/reload-btn-icon.png" alt="" />Показати ще</div>
                            </div>
                            <?php
                        }
                    ?>
                    <div class="ruler">
                        <?php
                            insertPagination($pagesCount, $currentPage - 1, $url_for_paging, $items_on_page);
                        ?>
                    </div>
                </div>
                <?php
            }
    }
