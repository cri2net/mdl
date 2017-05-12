

    <?php
        if (!defined('SHORT_FOOTER') || !SHORT_FOOTER) {
            ?>
            <footer>
                <div class="container">

                    <?php
                        if (defined('INDEX_FOOTER') && INDEX_FOOTER) {
                            ?>
                            <img src="<?= BASE_URL; ?>/assets/images/logo.png" class="logo" alt="ЦКС">
                            <div class="copyright pull-right">
                                Залишились питання?<br>
                                зателефонуйте нам<br>
                                <div class="phone">0 800 247 40 40</div>
                                <a href="mailto:zvernenya@src.kiev.ua">zvernenya@src.kiev.ua</a>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="row">
                                <div class="col-lg-10">
                                    <h2>Корисні посилання</h2>
                                    <div class="logos">
                                        <a href="#"><img src="<?= BASE_URL; ?>/assets/images/_partner-1.png" alt="Partner"></a>
                                        <a href="#"><img src="<?= BASE_URL; ?>/assets/images/_partner-2.png" alt="Partner"></a>
                                        <a href="#"><img src="<?= BASE_URL; ?>/assets/images/_partner-3.png" alt="Partner"></a>
                                        <a href="#"><img src="<?= BASE_URL; ?>/assets/images/_partner-4.png" alt="Partner"></a>
                                        <a href="#"><img src="<?= BASE_URL; ?>/assets/images/_partner-5.png" alt="Partner"></a>
                                        <a href="#"><img src="<?= BASE_URL; ?>/assets/images/_partner-6.png" alt="Partner"></a>
                                    </div>
                                    
                                    <nav class="navbar navbar-footer">
                                        <div id="navbar-footer" class="navbar-collapse collapse">
                                            <ul class="nav navbar-nav">
                                                <li><img src="<?= BASE_URL; ?>/assets/images/logo-inner.png" class="logo-footer" alt="ЦКС"></li>
                                                <li><a href="#">Угода користувача</a></li>
                                                <li><a href="#">Згода на обробку особистих даних</a></li>
                                                <li><a href="#">Питання до фахівця</a></li>
                                            </ul>
                                        </div>
                                    </nav>
                                </div>
                                <div class="col-lg-2">
                                    <div class="copyright pull-right">
                                        <h2>Ми на зв’язку</h2>
                                        <div class="phone">(044) 247-40-40</div>
                                        <a href="mailto:zvernenya@src.kiev.ua">zvernenya@src.kiev.ua</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    ?>

                </div>
            </footer>
            <?php
        }
    ?>

    <script>
        var BASE_URL = '<?= BASE_URL; ?>';
    </script>
    <?php
        if (USER_REAL_IP !== '127.0.0.1') {
            require_once(PROTECTED_DIR . '/scripts/yandex-metrika.php');
        }
    ?>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCTRSHf8sjMCfK9PHPJxjJkwrCIo5asIzE"></script>
    <script type="text/javascript" src="<?= BASE_URL; ?>/assets/js/plugins.js"></script>
    <script type="text/javascript" src="<?= BASE_URL; ?>/assets/js/scripts.js"></script>
    <script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
