<form onsubmit="return searchSubmit();" id="main-search" method="get" action="<?= BASE_URL; ?>/search/" class="search">
    <input type="text" placeholder="Пошук по сайту" value="" name="q" id="search">
</form>
<?php
    // зато один запрос к БД, а не два
    $sidebar_banners = PDO_DB::table_list(TABLE_PREFIX . 'hot_news', "`type` IN ('sidebar_banner', 'partners') AND is_active=1 AND LENGTH(img_filename) > 3", 'pos ASC');

    $banners = [];
    $partners = [];
    
    for ($i=0; $i < count($sidebar_banners); $i++) { 
        if ($sidebar_banners[$i]['type'] == 'sidebar_banner') {
            $banners[] = $sidebar_banners[$i];
        } else {
            $partners[] = $sidebar_banners[$i];
        }
    }

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

    if ($partners) {
        ?>
        <div class="partners">
            <div class="title">Нашi партнери</div>
            <?php
                foreach ($partners as $partner) {
                    $href = str_ireplace('{SITE_URL}', BASE_URL, $partner['link']);
                    $onclick = ($href == '') ? 'onclick="return false;"' : '';
                    ?>
                    <div class="item">
                        <span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/hot_news/<?= $partner['img_filename']; ?>');"></span>
                        <a <?= $onclick; ?> href="<?= htmlspecialchars($href, ENT_QUOTES); ?>"><?= htmlspecialchars($partner['title']); ?></a>
                    </div>
                    <?php
                }
            ?>
        </div>
        <?php
    }

    $links = PDO_DB::table_list(TABLE_PREFIX . 'useful_links', 'is_active=1', 'pos', '8');
    if (count($links) > 0) {
        ?>
        <div class="title links-title">Корисні посилання</div>
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
<div class="qr-code-box">
    <div class="inner">
        <img style="max-width:290px;" src="<?= BASE_URL; ?>/pic/sidebar/qr-code-resized.png" alt=""><br><br>
        <img src="<?= BASE_URL ?>/pic/sidebar/icon-scan.png" />&nbsp;&nbsp;&nbsp;Скануй та зберiгай
    </div>
</div>
