<!DOCTYPE html>
<html lang="uk">
<head>
<link rel="icon" href="http://kiev.gerc.ua/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="http://kiev.gerc.ua/favicon.ico" type="image/x-icon">
<title>ГіОЦ</title>
<meta charset="utf-8" />
<!--[if lt IE 9]><script>var e = ("breadcrumbs,slider,slide,submenu,heading,info,sidebar,menu,header,footer,news,content").split(','); for (var i = 0; i < e.length; i++) {document.createElement(e[i]);}</script><![endif]-->
<link href="<?= BASE_URL; ?>/style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&amp;subset=latin,cyrillic">
<script src="<?= BASE_URL; ?>/js/jquery-1.7.2.min.js"></script>
<script src="<?= BASE_URL; ?>/js/main.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery-ui.1.10.4.min.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.timers.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery_extends.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.maskedinput-1.4.1.js"></script>
<script src="<?= BASE_URL; ?>/js/jquery.prettyPhoto.js"></script>
<script>
	var BASE_URL = '<?= BASE_URL ?>';
</script>
<?php
	if (USER_REAL_IP == '127.0.0.1') {
		?><script type="text/javascript" src="http://localhost:35729/livereload.js"></script> <?php
	}
?>
<?php require_once(ROOT . '/protected/scripts/google-analytics.php'); ?>
<?php require_once(ROOT . '/protected/scripts/google-analytics-for-kiev.gerc.ua.php'); ?>
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
							<a class="tel" href="tel:+380442388025">(044) 238-80-25</a>
							<div class="darr-border"></div>
							<div class="darr"></div>
						</div>
						<div class="full-phone-box">
							<a class="tel" href="tel:+380442388027">(044) 238-80-27</a>
							<div class="work">
								<div class="work-content">
									<div class="line green">
										<div class="col">пн—чт</div>
										<div class="col-r">8:30—17:30</div>
									</div>
									<div class="line green">
										<div class="col">пт</div>
										<div class="col-r">8:30—16:15</div>
									</div>
									<div class="line yellow">
										<div class="col">перерва</div>
										<div class="col-r">12:30—13:15</div>
									</div>
									<div class="line">
										<div class="col">&nbsp;</div>
										<div class="col-r"><a href="<?= BASE_URL; ?>/contacts/#page-map-clock">Детальнiше</a></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
						if (Authorization::isLogin()) {
							?>
							<div class="account-link">
								<span class="border-bot">Особистий кабiнет</span>

								<div class="account-open">
									<div class="head-line">
										<div class="avatar">
											<div class="default-avatar"></div>
											<!-- <img src="http://placehold.it/34x34" alt=""> -->
										</div>
										<div class="username"><?= htmlspecialchars($__userData['name']) . '&nbsp;' . htmlspecialchars($__userData['lastname']); ?></div>
									</div>
									<div class="item objects">
										<a href="<?= BASE_URL; ?>/cabinet/objects/">Об'єкти</a>
									</div>
									<div class="item bank">
										<a href="#">Мої платежі</a>
									</div>
									<div class="item flash">
										<a href="#">Миттєві платежі</a>
									</div>
									<div class="item settings with-border">
										<a href="<?= BASE_URL; ?>/cabinet/settings/">Настройки профілю</a>
									</div>
									<div class="item faq with-border">
										<a href="<?= BASE_URL; ?>/cabhelp/">Он-лайн довідка</a>
									</div>
									<div class="item logout with-border">
										<a href="<?= BASE_URL; ?>/post/cabinet/logout/">Вихiд</a>
									</div>
								</div>
							</div>
							<?php
						} else {
							?><a href="<?= BASE_URL; ?>/cabinet/" class="account-link"><span class="border-bot">Особистий кабiнет</span></a> <?php
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
								<a href="#">Орієнтовний On-line розрахунок субсидій</a>
							</div>
							<div class="item">
								<a href="#">Розрахунок за показаннями квартирних приладів обліку</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>
		<content>
			<div class="inner <?= $__route_result['controller'] . '_' . $__route_result['action']; ?>">
				<?php require_once(ROOT . '/protected/scripts/breadcrumbs.php'); ?>
