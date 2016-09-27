<!DOCTYPE html>
<html lang="uk">
<head>
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
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&amp;subset=latin,cyrillic">
<?php
	if (USER_REAL_IP === '127.0.0.1') {
		?><script type="text/javascript" src="http://localhost:35729/livereload.js"></script> <?php
	} else {
		require_once(ROOT . '/protected/scripts/google-analytics.php');
	}
?>
<script src="<?= BASE_URL; ?>/js/jquery-1.7.2.min.js"></script>
</head>
<body>
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
										<a href="<?= BASE_URL; ?>/cabinet/objects/">Об’єкти</a>
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
		</header>
		<content>
			<div class="inner <?= (defined('HAVE_SIDEBAR') && !HAVE_SIDEBAR) ? 'wo-sidebar' : ''; ?> <?= $__route_result['controller'] . '_' . $__route_result['action']; ?>">
				<?php require_once(ROOT . '/protected/scripts/breadcrumbs.php'); ?>
