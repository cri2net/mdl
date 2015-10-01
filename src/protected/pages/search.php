<?php
    try {
        $_q = trim($_GET['q']);
        $items_on_page = 10;
        $news = [];
        $static_pages = [];
        
        $currentPage = 1;
        if (isset($_GET['page'])) {
            $currentPage = abs((int)$_GET['page']);
        }
        $currentPage = max(1, $currentPage);

        $sort_type = 'desc';
        if (isset($_GET['sort_type'])) {
            $sort_type = (strtolower($_GET['sort_type']) == 'asc') ? 'asc' : 'desc';
        }

        $order_by_col             = ['date' => 'created_at', 'title' => 'title', 'popularity' => 'views'];
        $order_by_col_static_page = ['date' => 'created_at', 'title' => 'h1',    'popularity' => 'views'];

        $order_by = 'date';
        if (isset($_GET['order_by'])) {
            $order_by = in_array($_GET['order_by'], $order_by_col)
                ? $_GET['order_by']
                : $order_by;
        }

        $news_count = News::search($_q, true);
        $pages_count = StaticPage::search($_q, true);
        $total_count = $news_count + $pages_count;

        $pagesCount = intval(ceil($total_count / $items_on_page));
        $from = ($currentPage * $items_on_page) - $items_on_page;
        $currentPage = min($currentPage, $pagesCount);
        $url_for_paging = BASE_URL . '/search/?q=' . rawurlencode($_q) . '&amp;sort_type='. $sort_type .'&amp;order_by='. $order_by .'&amp;page=';

        if ($from <= $news_count) {
            $news = News::search($_q, false, $order_by_col[$order_by], $sort_type, "$from, $items_on_page");
        }

        if ((count($news) < $items_on_page) && $pages_count) {
            $static_pages_from = $from + count($news) - $news_count;
            $static_pages_limit = $items_on_page - count($news);
            $static_pages = StaticPage::search($_q, false, $order_by_col_static_page[$order_by], $sort_type, "$static_pages_from, $static_pages_limit");
        }

    } catch (Exception $e) {
        $err = $e->getMessage();
    }
?>
<h1 class="search-title">Пошук за фразою «<span id="search-query-value"><?= htmlspecialchars($_q); ?></span>»</h1> <br>
<div class="search-sort">
    <a href="<?= BASE_URL; ?>/search/?q=<?= rawurlencode($_q); ?>&amp;sort_type=<?= ($order_by == 'date') ? (($sort_type == 'desc' ? 'asc' : 'desc')) : 'desc' ?>&amp;order_by=date" class="item <?= ($order_by == 'date') ? 'active' : ''; ?> by-date"><span class="text">За датою публікації</span><span class="order-by <?= $sort_type; ?>"></span></a>
    <a href="<?= BASE_URL; ?>/search/?q=<?= rawurlencode($_q); ?>&amp;sort_type=<?= ($order_by == 'title') ? (($sort_type == 'desc' ? 'asc' : 'desc')) : 'asc' ?>&amp;order_by=title" class="item <?= ($order_by == 'title') ? 'active' : ''; ?> by-title"><span class="text">За назвою сторінки</span><span class="order-by <?= $sort_type; ?>"></span></a>
    <a href="<?= BASE_URL; ?>/search/?q=<?= rawurlencode($_q); ?>&amp;sort_type=<?= ($order_by == 'popularity') ? (($sort_type == 'desc' ? 'asc' : 'desc')) : 'desc' ?>&amp;order_by=popularity" class="item <?= ($order_by == 'popularity') ? 'active' : ''; ?> by-popularity"><span class="text">За популярністю</span><span class="order-by <?= $sort_type; ?>"></span></a>
</div>
<?php
    if (isset($err) && $err) {
        ?>
        <h2 class="big-error-message"><?= $err; ?></h2>
        <?php
    }
?>
<div class="search-results">
    <?php
        if (!empty($news)) {
            ?>
            <h3 class="search-result-title news">Новини</h3>
            <ol>
                <?php

                foreach ($news as $item) {
                    $text = ($item['announce']) ? $item['announce'] : $item['text'];
                    $text = strip_tags($text);

                    if (mb_strlen($item['title'], 'UTF-8') > 75) {
                        $item['title'] = mb_substr($item['title'], 0, 75, 'UTF-8') . '...';
                    }

                    if (mb_strlen($text, 'UTF-8') > 430) {
                        $text = mb_substr($text, 0, 430, 'UTF-8') . '...';
                    }

                    ?>
                    <li>
                        <span class="decimal"><?= ++$from; ?>.</span>
                        <a class="title" href="<?= News::getNewsURL($item['id']); ?>"><?= htmlspecialchars($item['title']); ?></a> <br>
                        <span class="text"><?= $text; ?></span>
                    </li>
                    <?php
                }
                ?>
            </ol>
            <?php
        }

        if (!empty($static_pages)) {
            ?>
            <h3 class="search-result-title">Матеріали сайту</h3>
            <ol>
                <?php

                foreach ($static_pages as $item) {
                    $text = ($item['announce']) ? $item['announce'] : $item['text'];
                    $text = strip_tags($text);

                    if (mb_strlen($item['title'], 'UTF-8') > 75) {
                        $item['title'] = mb_substr($item['title'], 0, 75, 'UTF-8') . '...';
                    }

                    if (mb_strlen($text, 'UTF-8') > 430) {
                        $text = mb_substr($text, 0, 430, 'UTF-8') . '...';
                    }

                    ?>
                    <li>
                        <span class="decimal"><?= ++$from; ?>.</span>
                        <a class="title" href="<?= BASE_URL . StaticPage::getPath($item['id']); ?>"><?= htmlspecialchars($item['title']); ?></a> <br>
                        <span class="text"><?= $text; ?></span>
                    </li>
                    <?php
                }
                ?>
            </ol>
            <?php
        }
    ?>

    <div class="clear"></div>
    <?php
        if ($pagesCount > 1) {
            ?>
            <div class="align-center">
                <div class="ruler">
                    <?php
                        insertPagination($pagesCount, $currentPage - 1, $url_for_paging, $items_on_page);
                    ?>
                </div>
            </div>
            <?php
        }
    ?>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        if ($('#search-query-value').html() != "") {
            var words = $('#search-query-value').html().trim().replace(/ +/g, " ").split(" ");
            for (var i = 0; i < words.length; i++) {
                $('.search-results ol li').highlight(words[i]);
            }
        }
    });
</script>
