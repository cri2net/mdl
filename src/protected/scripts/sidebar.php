<form onsubmit="return searchSubmit();" id="main-search" method="get" action="<?= BASE_URL; ?>/search/" class="search">
    <input type="text" placeholder="Пошук по сайту" value="" name="q" id="search">
</form>
<?php
    $banners = PDO_DB::table_list(TABLE_PREFIX . 'hot_news', "`type`='sidebar_banner' AND is_active=1 AND LENGTH(img_filename) > 3", 'pos ASC');

    if ($banners) {
        ?>
        <div class="sidebar-slides">
            <?php
                foreach ($banners as $banner) {
                    $href = str_ireplace('{SITE_URL}', BASE_URL, $banner['link']);
                    $onclick = ($href == '') ? 'onclick="return false;"' : '';
                    ?>
                    <a <?= $onclick; ?> class="payonline" href="<?= htmlspecialchars($href, ENT_QUOTES); ?>">
                        <img style="max-width:290px;" src="<?= BASE_URL; ?>/db_pic/hot_news/<?= $banner['img_filename']; ?>" alt="">
                    </a>
                    <?php
                }
            ?>
        </div>
        <?php
    }
?>
<div class="partners">
    <div class="title">Нашi партнери</div>
    <div class="item">
        <span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/1.png');"></span>
        <a target="_blank" href="http://kievcity.gov.ua/">Сайт Київської міської державної адміністрації</a>
    </div>
    <div class="item">
        <span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/2.png');"></span>
        <br><a target="_blank" href="http://info.kyivcard.com.ua/main/">«Картка киянина»</a>
    </div>
    <div class="item">
        <span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/3.png');"></span>
        <br><a target="_blank" href="http://www.municipal.kiev.ua:8080/municipal/">«Ваш будинок»</a>
    </div>
</div>
<?php
    $links = PDO_DB::table_list(TABLE_PREFIX . 'useful_links', 'is_active=1', 'pos', '8');
    if (count($links) > 0) {
        ?>
        <div class="links">
            <?php
                foreach ($links as $link) {
                    $href = str_ireplace('{site_url}', BASE_URL, $link['link']);
                    if ($href == '') {
                        $href = '#';
                    }
                    ?><a target="<?= $link['target']; ?>" href="<?= $href; ?>"><?= htmlspecialchars($link['title']); ?></a> <?php
                }
            ?>
        </div>
        <?php
    }
?>
