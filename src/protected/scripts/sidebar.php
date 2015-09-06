<form onsubmit="return searchSubmit();" id="main-search" method="get" action="<?= BASE_URL; ?>/search/" class="search">
	<input type="text" placeholder="Пошук по сайту" value="" name="q" id="search">
</form>
<a class="payonline" href="<?= BASE_URL; ?>/infocenter/">
	<img src="<?= BASE_URL; ?>/pic/sidebar/payonline.png" alt="">
</a>
<a class="personal-account" href="https://www.personal-account.kiev.ua/" target="_blank">
	<img src="<?= BASE_URL; ?>/pic/sidebar/personal-account.png" alt="">
</a>
<div class="partners">
	<div class="title">Нашi партнери</div>
	<div class="item">
		<span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/1.png');"></span>
		<a href="#">Сайт Київської міської державної адміністрації</a>
	</div>
	<div class="item">
		<span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/2.png');"></span>
		<br><a href="#">«Картка киянина»</a>
	</div>
	<div class="item">
		<span class="img" style="background-image:url('<?= BASE_URL; ?>/db_pic/3.png');"></span>
		<br><a href="#">«Ваш будинок»</a>
	</div>
</div>
<?php
	$links = PDO_DB::table_list(TABLE_PREFIX . 'useful_links', 'is_active=1', 'pos', '8');
	if (count($links) > 0) {
		?>
		<div class="links">
			<?php
				foreach ($links as $link) {
					$href = str_ireplace('{site_url}', BASE_URL, $link['link']);
					if ($href == '') {
						$href = '#';
					}
					?><a target="<?= $link['target']; ?>" href="<?= $href; ?>"><?= htmlspecialchars($link['title']); ?></a> <?php
				}
			?>
		</div>
		<?php
	}
?>
