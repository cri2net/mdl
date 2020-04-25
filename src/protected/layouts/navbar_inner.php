<header class="strange_header">
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
                } elseif (defined('NAVBAR_FOR_PAYMENTS')) {
                    ?>
                    <div id="navbar-blue">
                        <ul class="nav navbar-nav">
                        </ul>
                    </div>
                    <?php
                } elseif (defined('NAVBAR_FOR_SETTINGS')) {
                    ?>
                    <div id="navbar-blue">
                        <ul class="nav navbar-nav">
                            <?php
                                require(PROTECTED_DIR . '/scripts/navbar_only_settings.php');
                            ?>
                        </ul>
                    </div>
                    <?php
                }
            ?>
    </nav>
</header>

<nav class="header__navigation navigation">
    <div class="navigation__container">
        <a href="#" class="navigation__img-link">
            <picture class="navigation__img">
                <source type="image/webp" srcset="image.webp">
                <source type="image/jpeg" srcset="image.jpg">
                <img src="#" alt="Місто для людей">
            </picture>
        </a>
        <ul class="navigation__list">
            <li class="navigation__item">
                <a href="#" class="navigation__link">
                    Мої об'єкти
                </a>
            </li>
            <li class="navigation__item">
                <a href="" class="navigation__link">
                    Мої платежі
                </a>
            </li>
            <li class="navigation__item">
                <a href="#" class="navigation__link">
                    Налаштування
                </a>
            </li>
            <li class="navigation__item">
                <a href="#" class="navigation__link">
                    Вихід
                </a>
            </li>
        </ul>
        <a href="#" class="navigation__link">
            Назад до МДЛ
        </a>
    </div>
</nav>

</header>