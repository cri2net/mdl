<footer class="footer">
    <section class="footer__top">
        <ul class="footer__list footer__list--outer">
            <li class="footer__item">
                <a href="#" class="footer__image-link">
                    <img src="<?= BASE_URL; ?>/assets/pic/mdl_logo_white.png"
                         alt="Місто для людей"
                         class="footer__img">
                </a>
            </li>
            <li class="footer__item footer__about-container">
                <h3 class="footer__title">
                    ТОВ "МІСТО ДЛЯ ЛЮДЕЙ"
                </h3>
                <p class="footer__text">
                    Послуги з утримання будинків і прибудинкової території. Цілодобова аварійна служба.
                </p>
            </li>
            <li class="footer__item">
                <h3 class="footer__title">
                    Контакти
                </h3>
                <ul class="footer__inner-list">
                    <li class="footer__inner-item">
                        <address class="footer__address">
                            Київ, вул. Болсуновська, 6
                        </address>
                    </li>
                    <li class="footer__inner-item">
                        <a href="tel:+380443334101"
                           class="footer__link footer__link--phone">
                            +38 (044) 333 41 01
                        </a>
                    </li>
                    <li class="footer__inner-item">
                        <a href="mailto:office@mdl.com.ua"
                           class="footer__link footer__link--mail">
                            office@mdl.com.ua
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </section>

    <section class="footer__container">
        <p class="footer__info footer__info--outer">
            2009 - <?= date('Y'); ?> © ООО "Місто для людей"
        </p>
        <div class="footer__payment-wrapper">
            <p class="footer__info footer__info--outer">
                Ми приймаємо до сплати
            </p>
            <div class="footer__payment footer__payment--outer">
                <img src="<?= BASE_URL; ?>/assets/pic/ps/visa.png"
                     alt="Visa"
                     class="footer__payment-img footer__payment-img--visa">
                <img src="<?= BASE_URL; ?>/assets/pic/ps/mastercard.png"
                     alt="Master Card"
                     class="footer__payment-img footer__payment-img--master">
                <img src="<?= BASE_URL; ?>/assets/pic/ps/prostir.png"
                     alt="Простір"
                     class="footer__payment-img footer__payment-img--prostir">
            </div>
        </div>
        <ul class="footer__socials">
            <li class="footer__socials-item">
                <a href="#" class="footer__socials-link">
                    <span class="visually-hidden">Facebook</span>
                    <img src="<?= BASE_URL; ?>/assets/pic/facebook.svg"
                         alt="Facebook"
                         class="footer__socials-img"
                         width="25"
                         height="22">
                </a>
            </li>
            <li class="footer__socials-item">
                <a href="#" class="footer__socials-link">
                    <span class="visually-hidden">Youtube</span>
                    <img src="<?= BASE_URL; ?>/assets/pic/youtube.svg"
                         alt="Youtube"
                         class="footer__socials-img"
                         width="25"
                         height="22">
                </a>
                </a>
            </li>
            <li class="footer__socials-item">
                <a href="#" class="footer__socials-link">
                    <span class="visually-hidden">Instagram</span>
                    <img src="<?= BASE_URL; ?>/assets/pic/instagram.svg"
                         alt="Instagram"
                         class="footer__socials-img"
                         width="25"
                         height="22">
                </a>
            </li>
        </ul>
    </section>
</footer>
        <script>
            var MAX_AMOUNT = '<?= MAX_AMOUNT; ?>';
            var BASE_URL = '<?= BASE_URL; ?>';
        </script>
        <script src="<?= BASE_URL; ?>/assets/js/scripts.js"></script>
        <script src="<?= BASE_URL; ?>/assets/js/main.js"></script>
    </div>
</body>
</html>
