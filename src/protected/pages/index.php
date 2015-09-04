<div class="home">
	<?php require_once(ROOT . '/protected/scripts/slider.php'); ?>
	<div class="features">
		<div class="feature left">
			<div class="title">Сплачуйте за послуги ЖКГ он-лайн</div>
			<ul>
				<li class="visa">Зручним способом
				<li class="cabinet">Контролюй сплати у особистому кабінеті
				<li class="account">Всі об'єкти під одним аккаунтом
				<li class="email">Отримуй email-повідомлення про оновлення рахунків
			</ul>
			<span class="cabinet-link align-right">
				<a href="<?= BASE_URL; ?>/cabinet/">Особистий кабiнет</a>
			</span>
		</div>
		<div class="feature right">
			<div class="title">Де сплатити послуги ЖКГ?</div>
			<ul>
				<li class="bank"><a href="<?= BASE_URL; ?>/banks/">Банки</a>
				<li class="terminal"><a href="<?= BASE_URL; ?>/terminals/">Термiнали самооблуговування</a>
			</ul>
		</div>
	</div>

	<?php
		$news = PDO_DB::table_list(News::TABLE, "is_actual=1", "created_at DESC", "2");
		if (count($news) > 0) {
			?>
			<h2 class="big-subtitle">Останнi новини</h1>
			<div class="news-list">
				<?php
					for ($i=0; $i < count($news); $i++) {
						
						$date = date('d ', $news[$i]['created_at']) . $MONTHS[date('n', $news[$i]['created_at'])]['ua'];
						if (date('Y') != date('Y', $news[$i]['created_at'])) {
							$date .= date(' Y', $news[$i]['created_at']);
						}

						if (mb_strlen($news[$i]['title'], 'UTF-8') > 50) {
							$news[$i]['title'] = mb_substr($news[$i]['title'], 0, 50, 'UTF-8') . '...';
						}

						?>
						<div class="news-item <?= ($i == 0) ? 'first' : ''; ?>">
							<h2 class="title"><?= htmlspecialchars($news[$i]['title']); ?></h2>
							<div class="date"><?= $date; ?></div>
							<div class="announce"><?= ($news[$i]['announce']); ?></div>
							<div class="details"><a href="<?= BASE_URL; ?>/news/<?= composeURLKey($news[$i]['title']); ?>_<?= $news[$i]['id']; ?>/">детальнiше...</a></div>
						</div>
						<?php
					}
				?>
			</div>
			<?php
		}
	?>
	<h2 class="big-subtitle green">Останнi матерiали для споживачiв</h1>
	<div class="news-list">
		<div class="news-item first">
			<h2 class="title">Про внесення змiн до норм споживання</h2>
			<div class="date">15 травня</div>
			<div class="announce">
				Кабінет Міністрів України постановив про внесення змін до норм споживання природного газу населенням у разі відсутності газових лічильників. Постанова КМУ від 29.04.2015 № 237
			</div>
			<div class="details"><a href="#">детальнiше...</a></div>
		</div>
		<div class="news-item">
			<h2 class="title">Куди звертатися для отримання субсидій?</h2>
			<div class="date">12 квiтня</div>
			<div class="announce">
				Процедура отримання субсидій здійснюється в районних управліннях праці та соціального захисту. На дошках оголошень під'їздах житлових будинків розміщено про адреси управлінь та їх контактні телефони.
			</div>
			<div class="details"><a href="#">детальнiше...</a></div>
		</div>
	</div>
</div>