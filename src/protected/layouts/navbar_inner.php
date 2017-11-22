<header>
    <nav class="navbar navbar-static">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar top-bar"></span>
                    <span class="icon-bar middle-bar"></span>
                    <span class="icon-bar bottom-bar"></span>
                </button>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li><a href="<?= BASE_URL ?>/about_cks/">Про нас</a></li>
                    <li><a href="<?= BASE_URL ?>/services_list_and_docs/">Перелік послуг</a></li>
                    <li><a href="<?= BASE_URL ?>/service-centers/">Сервісні центри</a></li>
                    <li><a href="<?= BASE_URL ?>/main_help/cabinet_faq/">Як сплачувати онлайн?</a></li>
                </ul>

                <?php
                    if (Authorization::isLogin()) {
                        ?>
                        <ul class="nav navbar-nav hidden-lg hidden-md">
                            <li><a href="<?= BASE_URL; ?>/cabinet/objects/">Мої об’єкти</a></li>
                            <li><a href="<?= BASE_URL; ?>/cabinet/instant-payments/">Каталог послуг</a></li>
                            <li><a href="#">Мої платежі</a></li>
                        </ul>
                        <?php
                            if (defined('NAVBAR_FOR_OBJECT_ITEM')) {
                                ?>
                                <ul class="nav navbar-nav hidden-lg hidden-md">
                                    <?php
                                        require(PROTECTED_DIR . '/scripts/navbar_only_obgect_item.php');
                                    ?>
                                </ul>
                                <?php
                            }
                        ?>
                        <?php
                    }
                ?>
            </div>  
            <div id="navbar-login">
                <ul>
                    <?php
                        if (Authorization::isLogin()) {
                            ?>
                            <li>
                                <a class="profile"><?= htmlspecialchars("{$__userData['name']} {$__userData['lastname']}"); ?></a>
                                <ul>
                                    <li class="item-1"><a href="<?= BASE_URL; ?>/cabinet/objects/">Об’єкти</a></li>
                                    <li class="item-2"><a href="<?= BASE_URL; ?>/cabinet/payments/">Мої платежі</a></li>
                                    <li class="item-3"><a href="<?= BASE_URL; ?>/main_help/cabinet_faq/">Онлайн довідка</a></li>
                                    <li class="item-4"><a href="<?= BASE_URL; ?>/cabinet/settings/">Редагувати профіль</a></li>
                                    <li class="item-5"><a href="<?= BASE_URL; ?>/post/cabinet/logout/">Вихід</a></li>
                                </ul>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li>
                                <a href="<?= BASE_URL; ?>/cabinet/login/" class="login">Увійти до системи</a>
                            </li>
                            <?php
                        }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-green">
        <div id="navbar-green" class="navbar-collapse collapse">
            <div class="container">
                <a href="<?= BASE_URL; ?>/"><img src="<?= BASE_URL; ?>/assets/images/logo-inner-top.png" class="logo" alt=""></a>
                <ul class="nav navbar-nav">
                    <li <?= strpos($route_path, 'cabinet/objects') > 0 ? 'class="active"' : '' ?> ><a href="<?= BASE_URL ?>/cabinet/objects/">Мої об’єкти</a></li>
                    <li <?= strpos($route_path, 'cabinet/instant-payments') > 0 ? 'class="active"' : '' ?> ><a href="<?= BASE_URL ?>/cabinet/instant-payments/">Каталог послуг</a></li>
                    <li <?= strpos($route_path, 'cabinet/payments') > 0 ? 'class="active"' : '' ?>><a href="<?= BASE_URL; ?>/cabinet/payments/">Мої платежі</a></li>
                </ul>
                <span class="phone">(044) 247-40-40</span>
            </div>      
        </div>
    </nav>
    <!-- <div class="container">
        <form id="search">
            <input type="text" placeholder="Швидкий пошук послуг">
            <input type="submit" value="Искать">
        </form>
    </div> -->
    <nav class="navbar navbar-blue">
        <div class="container">
            <?php
                if (defined('NAVBAR_FOR_OBJECT_ITEM')) {
                    ?>
                    <div id="navbar-blue" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <?php
                                require(PROTECTED_DIR . '/scripts/navbar_only_obgect_item.php');
                            ?>
                        </ul>
                    </div>
                    <?php
                } else if(defined('NAVBAR_FOR_PAYMENTS')) {
                    ?>
                    <div id="navbar-blue" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <?php
                                require(PROTECTED_DIR . '/scripts/navbar_only_payments.php');
                            ?>
                        </ul>
                    </div>
                    <?php
                } else if(defined('NAVBAR_FOR_SETTINGS')) {
                    ?>
                    <div id="navbar-blue" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <?php
                                require(PROTECTED_DIR . '/scripts/navbar_only_settings.php');
                            ?>
                        </ul>
                    </div>
                    <?php
                } else {
                    ?>
                    <div id="navbar-blue" class="navbar-services navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <li class="item-5 <?= strpos($route_path, 'cabinet/objects') > 0 ? 'active' : '' ?>"><a href="<?= BASE_URL ?>/cabinet/objects/">Комунальні<br>послуги</a></li>
                            <li class="item-6 <?= strpos($route_path, 'cabinet/instant-payments/phone') > 0 ? 'active' : '' ?>"><a href="<?= BASE_URL ?>/cabinet/instant-payments/phone/">Мобільний<br>зв'язок</a></li>
                            <li class="item-7 <?= strpos($route_path, 'cabinet/instant-payments/volia') > 0 ? 'active' : '' ?>"><a href="<?= BASE_URL ?>/cabinet/instant-payments/volia/">Інтернет та<br> телебачення</a></li>
                            <li class="item-8 inactive" ><a>Онлайн ігри та<br> сервіси</a></li>
                            <li class="item-9 <?= strpos($route_path, 'cabinet/instant-payments/cards') > 0 ? 'active' : '' ?>"><a href="<?= BASE_URL ?>/cabinet/instant-payments/cards/">Грошові<br> перекази</a></li>
                        </ul>
                    </div>
                    <?php
                }
            ?>
        </div>
    </nav>
</header>
