        </div>
        <?php
            if (!defined('HAVE_SIDEBAR') || HAVE_SIDEBAR) {
                ?>
                <div class="sidebar">
                    <?php require_once(ROOT . '/protected/scripts/sidebar.php'); ?>
                </div>
                <?php
            }
        ?>
    </content>
    <?php
        if (!defined('HAVE_SIDEBAR') || HAVE_SIDEBAR) {
            ?><div class="divider-conteiner"><div class="divider"></div></div> <?php
        }
    ?>
    <footer>
        <div class="inner">
            <div class="left">
                <img class="logo" src="<?= BASE_URL; ?>/pic/logo-footer.png" alt="">
                <div class="copyright">1963—<?= date('Y'); ?> © КП "ГіОЦ" <br> Всі права захищені</div>
            </div>
            <div class="center">
                <div class="links">
                    <a class="first" href="<?= BASE_URL; ?>/help/offers/">Угода користувача</a>
                    <a class="last" href="<?= BASE_URL; ?>/help/offers/">Згода на збір даних</a>
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
                        <div class="rss-container">
                            <a class="icon rss" href="<?= BASE_URL ?>/feed/"></a>— наш <a href="<?= BASE_URL ?>/feed/">RSS канал</a>
                        </div>
                    </div>
                </div>
                <div class="subscribe">
                    <div class="title">Будьте в курсі останніх новин:</div>
                    <div class="form-line">
                        <input name="email" type="email" id="subscribe_by_email" class="subscribe-email" placeholder="Електронна пошта">
                        <div onclick="subscribe_by_email();" class="btn small green bold">Підписатися</div>
                    </div>
                    <div class="clear"></div>
                    <span class="offer-text">
                        Підписуючись на розсилку, ви приймаєте <br>
                        <a href="<?= BASE_URL; ?>/help/offers/">Угоду користувача</a>
                    </span>
                </div>
            </div>
        </div>
    </footer>
</div>
<div id="back-top-wrapper"><span id="back-top"><a href="#top"></a></span></div>
<script src="<?= BASE_URL; ?>/js/main.js?m=<?= (is_readable(ROOT . "/js/main.js")) ? filemtime(ROOT . "/js/main.js") : ''; ?>"></script>
<script src="<?= BASE_URL; ?>/js/jquery-ui.1.10.4.min.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.timers.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery_extends.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.maskedinput-1.4.1.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.prettyPhoto.js"></script>
<script src="<?= BASE_URL; ?>/js/orphus.js" defer></script>
<script src="<?= BASE_URL; ?>/js/jqueryrotate.2.1.js" defer></script>
<script src="<?= BASE_URL; ?>/js/jquery.easydropdown.min.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.tooltipster.min.js"></script>
<script src="<?= BASE_URL; ?>/js/zxcvbn.js"></script>
<script>
    var BASE_URL = '<?= BASE_URL ?>';
</script>
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
            <?php
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

        // fade in #back-top
        $(window).scroll(function () {
            if ($(this).scrollTop() > 100) {
                $('#back-top').fadeIn();
            } else {
                $('#back-top').fadeOut();
            }
        });

        // scroll body to 0px on click
        $('#back-top a').click(function () {
            $('body,html').stop(false, false).animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        
        $(window).scroll();

        $('.tooltip').tooltipster({
            contentAsHTML: true
        });
    });
    $('a').each(function() {
        var a = new RegExp('/' + window.location.host + '/');
        if (!a.test(this.href) && this.href.length && (this.href != '#')) {
            if ($(this).attr('rel') !== 'prettyPhoto') {
                $(this).click(function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    window.open(this.href, '_blank');
                });
            }
        }
    });
</script>
<?php
    if (USER_REAL_IP !== '127.0.0.1') {
        require_once(ROOT . '/protected/scripts/yandex-metrika.php');
    }
?>
</body>
</html>