<!DOCTYPE html>
<html lang="ua">
<head>
<link rel="icon" href="http://kiev.gerc.ua/favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="http://kiev.gerc.ua/favicon.ico" type="image/x-icon">
<title>ГіОЦ</title>
<meta charset="utf-8" />

<link href="<?= BASE_URL; ?>/style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&amp;subset=latin,cyrillic">
<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/js/main.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery.tools.min.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery.popup.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery.autocomplete_2.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery-ui.1.10.4.min.js"></script>
<script type="text/javascript" src="<?= BASE_URL; ?>/js/jquery.timers.js"></script>
<?php
	if (USER_REAL_IP == '127.0.0.1') {
		?><script type="text/javascript" src="http://localhost:35729/livereload.js"></script> <?php
	}
?>
<?php require_once(ROOT . '/protected/scripts/google-analytics.php'); ?>
<?php require_once(ROOT . '/protected/scripts/google-analytics-for-kiev.gerc.ua.php'); ?>
</head>
<body>
	<header>
		<div class="top-line">
			<div class="inner">
				<a class="logo" href="<?= BASE_URL.'/'; ?>"></a>
				<div class="phone-block">
					<div class="number">
						(044) 238-80-25
						<div class="darr-border"></div>
						<div class="darr"></div>
					</div>
				</div>
				<a class="account-link" href="<?= BASE_URL.'/infocenter/'; ?>"><span>Особистий кабiнет</span></a>
			</div>
		</div>
		<div class="menu-block">
			<div class="inner">
				<?php require_once(ROOT . '/protected/scripts/menu.php'); ?>
				<div class="calc-btn">
					Онлайн-калькулятор
					<div class="darr"></div>
				</div>
			</div>
		</div>
	</header>
	<content>
		<div class="inner">
			<?php require_once(ROOT . '/protected/scripts/breadcrumbs.php'); ?>
