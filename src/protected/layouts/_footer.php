
    <?php
        use cri2net\php_pdo_db\PDO_DB;

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
                                <div class="phone">(044) 247 40 40</div>
                                <a href="mailto:zvernenya@src.kiev.ua">zvernennya@src.kiev.ua</a>
                            </div>
                            <?php
                        } else {
                            $useful_links = PDO_DB::table_list(TABLE_PREFIX . 'useful_links', "`is_active`=1", "`pos` ASC");
                            ?>
                            <div class="row">
                                <div class="col-lg-10">
                                    <?php
                                        if(count($useful_links)) {
                                        ?>
                                        <h2>Корисні посилання</h2>
                                        <div class="logos">
                                        <?php
                                            foreach($useful_links as $ul) {
                                            ?><a href="<?= $ul['link'] ?>" target="_blank"><img src="<?= BASE_URL; ?>/db_pic/useful-links/<?= $ul['filename'] ?>.jpg" alt="<?= htmlspecialchars($ul['title']) ?>" title="<?= htmlspecialchars($ul['title']) ?>"></a> <?php
                                            }
                                        ?>
                                        </div>
                                        <?php
                                        }
                                    ?>
                                    <nav class="navbar navbar-footer">
                                        <div id="navbar-footer" class="navbar-collapse collapse">
                                            <ul class="nav navbar-nav">
                                                <li><img src="<?= BASE_URL; ?>/assets/images/logo-inner.png" class="logo-footer" alt="ЦКС"></li>
                                                <li><a href="<?= BASE_URL ?>/services_list_and_docs/docs/user_agreement/">Угода користувача</a></li>
                                                <li><a href="<?= BASE_URL ?>/services_list_and_docs/docs/personal_data/">Згода на обробку особистих даних</a></li>
                                                <li><a href="<?= BASE_URL ?>/feedback/">Питання до фахівця</a></li>
                                            </ul>
                                        </div>
                                    </nav>
                                </div>
                                <div class="col-lg-2">
                                    <div class="copyright pull-right">
                                        <h2>Ми на зв’язку</h2>
                                        <div class="phone">(044) 247-40-40</div>
                                        <a href="mailto:zvernenya@src.kiev.ua">zvernennya@src.kiev.ua</a>
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
    <script type="text/javascript" src="<?= BASE_URL; ?>/assets/js/scripts.js"></script>
    <script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
