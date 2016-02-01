<!DOCTYPE html>
<html lang="uk">
<head>
<link rel="apple-touch-icon" sizes="57x57" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL; ?>/pic/favicon/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL; ?>/pic/favicon/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL; ?>/pic/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="<?= BASE_URL; ?>/pic/favicon/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL; ?>/pic/favicon/favicon-16x16.png">
<link rel="manifest" href="<?= BASE_URL; ?>/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="<?= BASE_URL; ?>/pic/favicon/ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!--[if lt IE 9]><style> input.txt, textarea.txt, select.txt { border:1px solid #444 !important; }</style><![endif]-->
<!--[if lt IE 9]><script>var e = ("breadcrumbs,slider,slide,submenu,heading,info,sidebar,menu,header,footer,news,content").split(','); for (var i = 0; i < e.length; i++) {document.createElement(e[i]);}</script><![endif]-->
<link href="<?= BASE_URL; ?>/style/style.css?m=<?= (is_readable(ROOT . "/style/style.css")) ? filemtime(ROOT . "/style/style.css") : ''; ?>" rel="stylesheet" type="text/css" />
<?php
	$file = ROOT . '/style/style-custom.css';
	if (file_exists($file) && (filesize($file) > 0)) {
		?>
		<link href="<?= BASE_URL; ?>/style/style-custom.css?m=<?= (is_readable(ROOT . "/style/style-custom.css")) ? filemtime(ROOT . "/style/style-custom.css") : ''; ?>" rel="stylesheet" type="text/css" />
		<?php
	}
?>
<title><?php require_once(ROOT . "/protected/scripts/seo/title.php"); ?></title>
<meta name="keywords" content="<?php require_once(ROOT . "/protected/scripts/seo/keywords.php"); ?>" />
<meta name="description" content="<?php require_once(ROOT . "/protected/scripts/seo/description.php"); ?>" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&amp;subset=latin,cyrillic">
<?php
	if (USER_REAL_IP === '127.0.0.1') {
		?><script type="text/javascript" src="http://localhost:35729/livereload.js"></script> <?php
	} else {
		require_once(ROOT . '/protected/scripts/google-analytics.php');
	}

    $tmp = PDO_DB::table_list(TABLE_PREFIX . 'text', "variable IN ('HEADER_PHONE', 'HEADER_PHONE_SECOND', 'HEADER_WORK')");
    for ($i=0; $i < count($tmp); $i++) { 
        $_tmp[$tmp[$i]['variable']] = $tmp[$i]['text'];
    }

    switch ($__route_result['controller'] . "/" . $__route_result['action']) {
        case 'page/tender':
            define('HAVE_SIDEBAR', false);
    }
?>
<script src="<?= BASE_URL; ?>/js/jquery-1.7.2.min.js"></script>
<meta name="google-site-verification" content="0UGlupvvEO3lxBitGmrsRE3uxascX123gKbj9O5k-KY" />
<meta name="google-site-verification" content="ba0Oq4krG43JMDsAVoNBcrMm7qYzTbClU1emrsqcKYw" />
</head>
<body>
	<?php require_once(ROOT . '/protected/scripts/browser-warning.php'); ?>
	<div class="main-conteiner">
		<header>
			<div class="top-line">
				<div class="inner">
					<a class="logo" href="<?= BASE_URL . '/'; ?>"></a>
					<div class="phone-block">
						<div class="phone-icon"></div>
						<div class="number">
							<?= $_tmp['HEADER_PHONE']; ?>
							<div class="darr-border"></div>
							<div class="darr"></div>
						</div>
						<div class="full-phone-box">
							<?= $_tmp['HEADER_PHONE_SECOND']; ?>
							<div class="work">
								<div class="work-content">
									<?= $_tmp['HEADER_WORK']; ?>
									<div class="line">
										<div class="col">&nbsp;</div>
										<div class="col-r"><a href="<?= BASE_URL; ?>/contacts/#page-map-clock">Детальніше</a></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
						if (Authorization::isLogin()) {
							?>
							<div class="account-link logged-in">
								<div class="account-open">
									<div class="head-line">
										<div class="avatar">
											<div class="default-avatar"></div>
											<!-- <img src="http://placehold.it/34x34" alt=""> -->
										</div>
										<div class="username"><span><?= htmlspecialchars($__userData['name']) . '&nbsp;' . htmlspecialchars($__userData['lastname']); ?></span></div>
									</div>
									<div class="item objects">
										<a href="<?= BASE_URL; ?>/cabinet/objects/">Об'єкти</a>
									</div>
									<div class="item bank">
										<a href="<?= BASE_URL; ?>/cabinet/payments/">Мої платежі</a>
									</div>
									<div class="item flash">
										<a href="<?= BASE_URL; ?>/cabinet/instant-payments/">Миттєві платежі</a>
									</div>
									<div class="item settings with-border">
										<a href="<?= BASE_URL; ?>/cabinet/settings/">Налаштування профілю</a>
									</div>
									<div class="item faq with-border">
										<a href="<?= BASE_URL; ?>/help/faq_cabinet/">Онлайн довідка</a>
									</div>
									<div class="item logout with-border">
										<a href="<?= BASE_URL; ?>/post/cabinet/logout/">Вихід</a>
									</div>
								</div>
							</div>
							<?php
						} else {
							?><a href="<?= BASE_URL; ?>/cabinet/" class="account-link"><span class="border-bot">Особистий кабінет</span></a> <?php
						}
					?>
				</div>
			</div>
			<div class="menu-block">
				<div class="inner">
					<?php require_once(ROOT . '/protected/scripts/menu.php'); ?>
					<div class="calc-btn">
						<span class="title">Онлайн-калькулятор</span>
						<div class="icon"></div>
						<div class="darr"></div>
						<div class="calc-open">
							<div class="item">
								<a href="<?= BASE_URL ?>/calc-subsidies/">Орієнтовний онлайн розрахунок субсидій</a>
							</div>
							<div class="item">
								<a href="<?= BASE_URL ?>/calc-devices/">Розрахунок за показаннями квартирних приладів обліку</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>
		<content>
			<div class="inner <?= (defined('HAVE_SIDEBAR') && !HAVE_SIDEBAR) ? 'wo-sidebar' : ''; ?> <?= $__route_result['controller'] . '_' . $__route_result['action']; ?>">
				<?php
					switch ($__route_result['controller'] . "/" . $__route_result['action']) {
						case 'page/news':
						case 'page/news-item':
							require_once(ROOT . '/protected/scripts/slider.php');
							break;
					}
				?>
				<?php require_once(ROOT . '/protected/scripts/breadcrumbs.php'); ?>
