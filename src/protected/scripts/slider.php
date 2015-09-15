<?php

$slides = PDO_DB::table_list(TABLE_PREFIX . 'hot_news', "`type`='index_slide' AND is_active=1 AND LENGTH(img_filename) > 3", 'pos ASC');

if ($slides) {
    ?>
    <slider>
        <div class="slider-btn next-btn" onclick="next_slide_rotate_index();"></div>
        <div class="slider-btn prev-btn" onclick="prev_slide_rotate_index();"></div>
        <?php
            for ($i=0; $i < count($slides); $i++) {
                $href = str_ireplace('{SITE_URL}', BASE_URL, $slides[$i]['link']);
                ?>
                <a id="slide_<?= $i; ?>" href="<?= htmlspecialchars($href, ENT_QUOTES); ?>" style="display:<?= ($i == 0) ? 'block' : 'none'; ?>">
                    <img src="<?= BASE_URL; ?>/db_pic/hot_news/<?= $slides[$i]['img_filename']; ?>" alt="" />
                </a>
                <?php
            }
        ?>
        <div class="bullets">
            <?php
                for ($i=0; $i < count($slides); $i++) {
                    ?><div class="bullet <?= ($i == 0) ? 'active' : ''; ?>" id="bullet_<?= $i; ?>" onclick="jump_to_slide(<?= $i; ?>);"></div> <?php
                }
            ?>
        </div>
        <script type="text/javascript">
            var slide_count = <?= count($slides); ?>;
            $(document).ready(function(){
                $('.bullets').everyTime(4000, 'slider_bullets', function() {
                    next_slide_rotate_index();
                });
            });
        </script>
    </slider>
    <?php
}
