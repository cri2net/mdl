        </div>
        <div class="sidebar">
            <?php require_once(ROOT . '/protected/scripts/sidebar.php'); ?>
        </div>
    </content>
    <div class="divider-conteiner"><div class="divider"></div></div>
    <footer>
        <div class="inner">
            <div class="left">
                <img class="logo" src="<?= BASE_URL; ?>/pic/logo-footer.png" alt="">
                <div class="copyright">1963—<?= date('Y'); ?> © КП "ГіОЦ" <br> Всі права захищені</div>
            </div>
            <div class="center">
                <div class="links">
                    <a href="#">Угода користувача</a>
                    <a href="#">Угода щодо конфиденційності</a>
                </div>
                <div class="mistakes">
                    Якщо Ви побачили граматичну або синтаксичну <br>
                    помилку, будь ласка, виділіть її мишкою <br>
                    та натисніть <a>Ctrl + Enter</a>
                </div>
            </div>
            <div class="right">
                <div class="social">
                    Ми стали ближче:
                    <div class="icons">
                        <?php
                            $icons = PDO_DB::table_list(TABLE_PREFIX . 'social', "is_active=1", 'pos ASC');
                            for ($i=0; $i < count($icons); $i++) {
                                ?><a class="icon <?= $icons[$i]['key']; ?> <?= ($i == 0) ? 'first' : ''; ?> <?= ($i == count($icons) - 1) ? 'last' : ''; ?>" target="<?= $icons[$i]['target']; ?>" href="<?= htmlspecialchars($icons[$i]['link'], ENT_QUOTES); ?>" title="<?= htmlspecialchars($icons[$i]['title'], ENT_QUOTES); ?>"></a> <?php
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>
<script src="<?= BASE_URL; ?>/js/orphus.js"></script>
<?php
    preg_match('/\?error_id=([0-9]+)$/', $_SERVER['REQUEST_URI'], $error_matches);
    
    if (isset($error_matches[1])) {
        $error = PDO_DB::row_by_id(TABLE_PREFIX . 'text_errors', (int)$error_matches[1]);
        if ($error != null) {
            $search_text = json_decode($error['raw_data']);
            $search_text = json_encode($search_text->c_sel);
            ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    $('body').highlight(<?= $search_text; ?>);
                });
            </script>
            <?
        }
    }
?>
<script type="text/javascript">
    $(document).ready(function(){
        $("a[rel^='prettyPhoto']").prettyPhoto({
            showTitle: false,
            deeplinking: false,
            slideshow: false,
            animation_speed: 0,
            theme: 'facebook',
            show_title:false,
            overlay_gallery:false,
            social_tools: ''
        });
    });
</script>
<?php require_once(ROOT . '/protected/scripts/yandex-metrika.php'); ?>
<?php require_once(ROOT . '/protected/scripts/yandex-metrika-for-kiev.gerc.ua.php'); ?>
</body>
</html>