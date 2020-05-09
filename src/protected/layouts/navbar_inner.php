<?php
    if (Authorization::isLogin()) {

        $navs = [
            '/cabinet/objects/'     => 'Мої об’єкти',
            '/cabinet/payments/'    => 'Мої платежі',
            '/cabinet/settings/'    => 'Налаштування',
            '/post/cabinet/logout/' => 'Вихід',
        ];
    } else {

        $navs = [
            '/cabinet/login/'        => 'Вхід',
            '/cabinet/registration/' => 'Зареєструватися',
            '/cabinet/restore/'      => 'Відновлення доступу',
        ];
    }
?>

<nav class="header__navigation navigation">
    <div class="navigation__container">
        <a href="<?= BASE_URL; ?>/" class="navigation__img-link">
            <picture>
                <source type="image/webp"
                        srcset="<?= BASE_URL; ?>/assets/pic/mdl_logo_optimize.webp">
                <source type="image/png"
                        srcset="<?= BASE_URL; ?>/assets/pic/mdl_logo_optimize.png">
                <img src="<?= BASE_URL; ?>/assets/pic/mdl_logo_optimize.png"
                     alt="Місто для людей"
                     class="navigation__img">
            </picture>
        </a>

        <ul class="navigation__list">
            <?php
                foreach ($navs as $url => $title) {
                    ?>
                    <li class="navigation__item">
                        <a href="<?= BASE_URL . $url; ?>" class="navigation__link">
                            <?= $title; ?>
                        </a>
                    </li>
                    <?php
                }
            ?>
        </ul>
        <div class="menu">
            <span class="menu-global menu-top"></span>
            <span class="menu-global menu-middle"></span>
            <span class="menu-global menu-bottom"></span>
        </div>
        <ul class="navigation__mobile-list">
            <?php
                foreach ($navs as $url => $title) {
                    ?>
                    <li class="navigation__mobile-item">
                        <a href="<?= BASE_URL . $url; ?>" class="navigation__link navigation__mobile-link">
                            <?= $title; ?>
                        </a>
                    </li>
                    <?php
                }
            ?>
        </ul>
        <a href="https://mdl.com.ua/" class="navigation__link">
            Назад до МДЛ
        </a>
    </div>

    <?php
        if (defined('NAVBAR_FOR_OBJECT_ITEM')) {
            ?>
            <div class="inner-nav" id="navbar-blue">
                <ul class="inner-nav__list">
                    <?php
                        require(PROTECTED_DIR . '/scripts/navbar_only_obgect_item.php');
                    ?>
                </ul>
            </div>
            <?php
        }
    ?>
</nav>

</header>
