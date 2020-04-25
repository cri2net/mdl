<?php
    $_baseRoute = $__route_result['controller'] . "/" . $__route_result['action'];
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/images/favicon.png" >
<link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/main.css">
<!--[if lt IE 9]><style> input.txt, textarea.txt, select.txt { border:1px solid #444 !important; }</style><![endif]-->
<!--[if lt IE 9]><script>const e = ("breadcrumbs,slider,slide,submenu,heading,info,sidebar,menu,header,footer,news,content").split(','); for (let i = 0; i < e.length; i++) {document.createElement(e[i]);}</script><![endif]-->

<title><?php require_once(PROTECTED_DIR . '/scripts/seo/title.php'); ?></title>
<!--<script src="<?= BASE_URL; ?>/assets/js/modernizr-2.6.2.min.js"></script>-->
<script src="<?= BASE_URL; ?>/assets/js/plugins.min.js"></script>

<?php
    if (USER_REAL_IP === '127.0.0.1') {
        ?><script src="http://localhost:35729/livereload.js"></script> <?php
    } else {
        require_once(PROTECTED_DIR . '/scripts/google-analytics.php');
    }
?>
</head>
<body>
    <header class="header">
        <div class="header__container">
            <ul class="header__list header__contact-info">
                <li class="header__item">
                    <a href="tel:+380443334101" class="header__link">
                        +38 (044) 333 41 01
                    </a>
                </li>
                <li class="header__item">
                    <a href="mailto:office@mdl.com.ua" class="header__link">
                        office@mdl.com.ua
                    </a>
                </li>
                <li class="header__item">
                    <address class="header__address">
                        Київ, вул. Болсуновська, 6
                    </address>
                </li>
            </ul>

            <ul class="header__list header__socials">
                <li class="header__socials-item">
                    <a href="#" class="header__socials-img-link">
                        <span class="visually-hidden">Facebook</span>
                        <img src="./src/pic/facebook.svg" alt="Facebook">
                    </a>
                </li>
                <li class="header__socials-item">
                    <a href="#" class="header__socials-img-link">
                        <span class="visually-hidden">Youtube</span>
                        <img src="./src/pic/youtube.svg" alt="Youtube">
                    </a>
                </li>
                <li class="header__socials-item">
                    <a href="#" class="header__socials-img-link">
                        <span class="visually-hidden">Instagram</span>
                        <img src="./src/pic/instagram.svg" alt="Instagram">
                    </a>
                </li>
            </ul>
        </div>
