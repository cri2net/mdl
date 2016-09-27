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
    <footer>
    </footer>
</div>
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
<script type="text/javascript">
    $(document).ready(function(){
        $('.tooltip').tooltipster({
            contentAsHTML: true
        });
    });
</script>
<?php
    if (USER_REAL_IP !== '127.0.0.1') {
        require_once(ROOT . '/protected/scripts/yandex-metrika.php');
    }
?>
</body>
</html>