<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--[if lt IE 9]><style> input.txt, textarea.txt, select.txt { border:1px solid #444 !important; }</style><![endif]-->
<!--[if lt IE 9]><script>var e = ("breadcrumbs,slider,slide,submenu,heading,info,sidebar,menu,header,footer,news,content").split(','); for (var i = 0; i < e.length; i++) {document.createElement(e[i]);}</script><![endif]-->
<link href="<?= BASE_URL; ?>/css/style.css?m=<?= (is_readable(ROOT . "/css/style.css")) ? filemtime(ROOT . "/css/style.css") : ''; ?>" rel="stylesheet" type="text/css" />
<title><?php require_once(ROOT . "/protected/scripts/seo/title.php"); ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&amp;subset=latin,cyrillic">
<?php
    if (USER_REAL_IP === '127.0.0.1') {
        ?><script type="text/javascript" src="http://localhost:35729/livereload.js"></script> <?php
    } else {
        require_once(ROOT . '/protected/scripts/google-analytics.php');
    }
?>
<script src="<?= BASE_URL; ?>/js/jquery-1.7.2.min.js"></script>

<script src="<?= BASE_URL; ?>/js/main.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery-ui.1.10.4.min.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery_extends.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.maskedinput-1.4.1.js"></script>
<script src="<?= BASE_URL; ?>/js/jqueryrotate.2.1.js" defer></script>
<script src="<?= BASE_URL; ?>/js/jquery.easydropdown.min.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.tooltipster.min.js"></script>
<script src="<?= BASE_URL; ?>/js/zxcvbn.js"></script>
</head>
<body>
