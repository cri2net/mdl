<header>
    <nav class="navbar navbar-green">
        <div id="navbar-green">

                <ul class="nav navbar-nav">
                    <li <?= strpos($route_path, 'cabinet/objects') > 0 ? 'class="active"' : '' ?> ><a class=" item-objects" href="<?= BASE_URL ?>/cabinet/objects/">Мої об’єкти</a></li>
                    <li <?= strpos($route_path, 'cabinet/payments') > 0 ? 'class="active"' : '' ?>><a class=" item-payments" href="<?= BASE_URL; ?>/cabinet/payments/">Мої платежі</a></li>
                </ul> 
        </div>
    </nav>
    <nav class="navbar navbar-blue">
            <?php
                if (defined('NAVBAR_FOR_OBJECT_ITEM')) {
                    ?>
                    <div id="navbar-blue">
                        <ul class="nav navbar-nav">
                            <?php
                                require(PROTECTED_DIR . '/scripts/navbar_only_obgect_item.php');
                            ?>
                        </ul>
                    </div>
                    <?php
                } else if(defined('NAVBAR_FOR_PAYMENTS')) {
                    ?>
                    <div id="navbar-blue">
                        <ul class="nav navbar-nav">
                            <?php/*
                                require(PROTECTED_DIR . '/scripts/navbar_only_payments.php');
                                */
                            ?>
                        </ul>
                    </div>
                    <?php
                } else if(defined('NAVBAR_FOR_SETTINGS')) {
                    ?>
                    <div id="navbar-blue">
                        <ul class="nav navbar-nav">
                            <?php
                                require(PROTECTED_DIR . '/scripts/navbar_only_settings.php');
                            ?>
                        </ul>
                    </div>
                    <?php
                } else {
                    /*
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
                    */
                }
            ?>
    </nav>
</header>
