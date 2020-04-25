<footer class="footer">
    <ul class="footer__list">
        <li class="footer__item">
            <a href="#" class="footer__image-link">
                <picture class="footer__img">
                    <source type="image/webp" srcset="image.webp">
                    <source type="image/jpeg" srcset="image.jpg">
                    <img src="#" alt="Місто для людей">
                </picture>
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
            <ul class="footer__list">
                <li class="footer__item">
                    <a href="tel:+380443334101" class="footer__link">+38 (044) 333 41 01</a>
                </li>
                <li class="footer__item">
                    <a href="mailto:office@mdl.com.ua" class="footer__link">
                        office@mdl.com.ua
                    </a>
                </li>
                <li class="footer__item">
                    <address class="footer__address">
                        Київ, вул. Болсуновська, 6
                    </address>
                </li>
            </ul>
        </li>
    </ul>

    <div class="footer__container">
        <p class="footer__info">
            2009 - <?= date('Y'); ?> © ООО "Місто для людей"
        </p>
        <p class="footer__info">
            Ми приймаємо Visa, Mastercard, Простір
        </p>
        <ul class="footer__socials">
            <li class="footer__socials-item">
                <a href="#" class="footer__socials-link">Facebook</a>
            </li>
            <li class="footer__socials-item">
                <a href="#" class="footer__socials-link">Youtube</a>
            </li>
            <li class="footer__socials-item">
                <a href="#" class="footer__socials-link">Instagram</a>
            </li>
        </ul>
    </div>

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
