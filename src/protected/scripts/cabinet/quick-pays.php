<body>
<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>
<div class="container">
<content>
<div class="portlet">
<div class="block block-quick-pays-short">
	<div class="row">
		<div class="col-md-4" >
			<a class="item cks" href="<?= BASE_URL; ?>/cabinet/instant-payments/cks/" class="cks">Сплата послуг ЦКС</a>
		</div>
		<div class="col-md-4" >
			<a href="<?= BASE_URL; ?>/cabinet/instant-payments/dai/" class="item police">Штрафи за порушення ПДР</a>
		</div>
		<div class="col-md-4" >
			<a href="<?= BASE_URL; ?>/cabinet/instant-payments/kindergarten/" class="item baby">Сплата за дитячий садок</a>
		</div>
		<div class="col-md-4" >
			<a href="<?= BASE_URL; ?>/cabinet/instant-payments/cards/" class="item cards">Перекази з карти на карту</a>
		</div>
		<div class="col-md-4" >
			<a href="<?= BASE_URL; ?>/cabinet/instant-payments/phone/" class="item phone">Телефон та інтернет</a>
		</div>
		<div class="col-md-4" >
			<a href="<?= BASE_URL; ?>/cabinet/instant-payments/volia/" class="item volia">ВОЛЯ ТV & internet</a>
		</div>
	</div>
</div>
</div>
</content>
</div>
