<form onsubmit="return searchSubmit();" id="main-search" method="get" action="<?= BASE_URL; ?>/search/" class="search">
	<input type="text" placeholder="Пошук по сайту" value="" name="q" id="search">
</form>
<a class="payonline" href="<?= BASE_URL; ?>/infocenter/">
	<img src="<?= BASE_URL; ?>/pic/sidebar/payonline.png" alt="">
</a>
<a class="payonline" href="https://www.personal-account.kiev.ua/" target="_blank">
	<img src="<?= BASE_URL; ?>/pic/sidebar/personal-account.png" alt="">
</a>
<div class="partners">
	<div class="title">Нашi партнери</div>
	<div class="item">
		<a href="#">
			<span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/1.png');"></span>
			Сайт Київської міської державної адміністрації
		</a>
	</div>
	<div class="item">
		<a href="#">
			<span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/2.png');"></span>
			«Картка киянина»
		</a>
	</div>
	<div class="item">
		<a href="#">
			<span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/3.png');"></span>
			«Ваш будинок»
		</a>
	</div>
</div>