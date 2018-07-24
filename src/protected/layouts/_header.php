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
<!--[if lt IE 9]><style> input.txt, textarea.txt, select.txt { border:1px solid #444 !important; }</style><![endif]-->
<!--[if lt IE 9]><script>var e = ("breadcrumbs,slider,slide,submenu,heading,info,sidebar,menu,header,footer,news,content").split(','); for (var i = 0; i < e.length; i++) {document.createElement(e[i]);}</script><![endif]-->

<title><?php require_once(PROTECTED_DIR . '/scripts/seo/title.php'); ?></title>

<link href="<?= BASE_URL; ?>/assets/css/bootstrap-grid.css" rel="stylesheet">
<link href="<?= BASE_URL; ?>/assets/css/font-awesome.css" rel="stylesheet">
<link href="<?= BASE_URL; ?>/assets/css/swiper.css" rel="stylesheet">
<link href="<?= BASE_URL; ?>/assets/css/cabinet.css?m=<?= (is_readable(ROOT . "/assets/css/landing.css")) ? filemtime(ROOT . "/assets/css/cabinet.css") : ''; ?>" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Fjalla+One%7COpen+Sans:300,400,600,700" rel="stylesheet">
<script type="text/javascript" src="<?= BASE_URL; ?>/assets/js/modernizr-2.6.2.min.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/assets/js/plugins.min.js"></script>
<script>
    $(function() { var scroll = new SmoothScroll('a[href*="#"]', {speed: 500})});

    function getDocHeight() {

        var iframe_border = 0;

        return $('#all_body_container').outerHeight() + iframe_border;

        if (window.GetDocumentHeight) {
            return window.GetDocumentHeight() + iframe_border;
        }
        var D = document;
        return Math.max(
            D.body.scrollHeight, D.documentElement.scrollHeight,
            D.body.offsetHeight, D.documentElement.offsetHeight,
            D.body.clientHeight, D.documentElement.clientHeight
        ) + iframe_border;
    };

    window.last_height = false;

    $(document).ready(function(){

        // $(window).resize(function() {
        //     window.parent.postMessage(String(getDocHeight()), '<?= KMDA_ORDER_URL; ?>');
        //     console.log(getDocHeight());
        // });
        // $(window).resize();

        setInterval(function(){
            if (window.last_height !== getDocHeight()) {
                window.parent.postMessage(String(getDocHeight()), '<?= KMDA_ORDER_URL; ?>');
                window.last_height = getDocHeight();
            }
        }, 250);
    });
</script>

<?php
    if (USER_REAL_IP === '127.0.0.1') {
        ?><script type="text/javascript" src="http://localhost:35729/livereload.js"></script> <?php
    } else {
        require_once(PROTECTED_DIR . '/scripts/google-analytics.php');
    }
?>
<script src='https://www.google.com/recaptcha/api.js'></script>
</head>
