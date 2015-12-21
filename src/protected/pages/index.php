<div class="home">
	<?php require_once(ROOT . '/protected/scripts/slider.php'); ?>
	<div class="features">
		<div class="feature left">
			<div class="title">Сплачуйте за послуги ЖКГ онлайн</div>
			<ul>
				<li class="visa">Зручним способом
				<li class="cabinet">Контролюй сплати в особистому кабінеті
				<li class="account">Всі об'єкти під одним аккаунтом
				<li class="email">Отримуй email-повідомлення про оновлення рахунків
			</ul>
			<span class="cabinet-link align-right">
				<a href="<?= BASE_URL; ?>/cabinet/">Особистий кабінет</a>
			</span>
		</div>
		<div class="feature right">
			<div class="title">Де сплатити послуги ЖКГ?</div>
			<ul>
				<!-- li class="bank"><a href="<?= BASE_URL; ?>/foruser/banks/">Банки</a -->
				<li class="terminal"><a href="<?= BASE_URL; ?>/foruser/terminals/">Термінали самооблуговування</a>
			</ul>
		</div>
	</div>

	<?php
		$news = PDO_DB::table_list(News::TABLE, "is_actual=1", "created_at DESC", "2");
		if (count($news) > 0) {
			?>
			<h2 class="big-subtitle">Останні новини</h2>
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
							<div class="details"><a href="<?= BASE_URL; ?>/news/<?= composeURLKey($news[$i]['title']); ?>_<?= $news[$i]['id']; ?>/">детальніше...</a></div>
						</div>
						<?php
					}
				?>
			</div>
			<?php
		}


		// статьи со всех подразделов Правові документи
		// Реализация жестяковая, конечно.
		
		$law = StaticPage::getByKey('law', 0);
		if ($law) {
			$arr_ids = [$law['id']];
			$pages = StaticPage::getChildren($law['id']);

			for ($i=0; $i < count($pages); $i++) {
				$arr_ids[] = $pages[$i]['id'];
			}

			$in_query = trim(implode($arr_ids, ','), ',');

			$pages = PDO_DB::table_list(StaticPage::TABLE, "is_active=1 AND idp IN ($in_query)", "created_at DESC", "2");
		}

		if (count($pages) > 0) {
			?>
			<h2 class="big-subtitle green">Останні матеріали</h2>
			<div class="news-list">
				<?php
					for ($i=0; $i < count($pages); $i++) {

						$date = date('d ', $pages[$i]['created_at']) . $MONTHS[date('n', $pages[$i]['created_at'])]['ua'];
						if (date('Y') != date('Y', $pages[$i]['created_at'])) {
							$date .= date(' Y', $pages[$i]['created_at']);
						}

						if (mb_strlen($pages[$i]['h1'], 'UTF-8') > 50) {
							$pages[$i]['h1'] = mb_substr($pages[$i]['h1'], 0, 50, 'UTF-8') . '...';
						}

						?>
						<div class="news-item <?= ($i == 0) ? 'first' : ''; ?>">
							<h2 class="title"><?= htmlspecialchars($pages[$i]['h1']); ?></h2>
							<div class="date"><?= $date; ?></div>
							<div class="announce"><?= ($pages[$i]['announce']); ?></div>
							<div class="details"><a href="<?= BASE_URL . StaticPage::getPath($pages[$i]['id']); ?>">детальніше...</a></div>
						</div>
						<?php
					}
				?>
			</div>
			<?php
		}
	?>
</div>
