<?php
	$breadcrumbs = array(
		array('title' => 'ГіОЦ', 'link' => '/')
	);

	switch($__route_result['controller'] . "/" . $__route_result['action']) {
		case 'page/index':
			$breadcrumbs[] = array('title' => 'Головна');
			break;

		case 'page/about':
			$breadcrumbs[] = array('title' => 'Про ГіOЦ');
			break;

		case 'page/foruser':
			$breadcrumbs[] = array('title' => 'Споживачу');
			break;


		case 'page/cabinet':
			$breadcrumbs[] = array('title' => 'Особистий кабiнет', 'link' => '/cabinet/');

			switch ($__route_result['values']['subpage']) {

				case 'registration':
					$breadcrumbs[] = array('title' => 'Реєстрація');
					break;

				case 'login':
					$breadcrumbs[] = array('title' => 'Вхід');
					break;

				case 'objects':
					$breadcrumbs[] = array('title' => 'Об\'єкти');
					break;
			}

			break;


		case 'page/contacts':
			$breadcrumbs[] = array('title' => 'Контакти');
			break;

		case 'page/news':
			$breadcrumbs[] = array('title' => 'Новини');
			break;
		case 'page/news-item':
			$breadcrumbs[] = array('title' => 'Новини', 'link' => '/news/');
			$breadcrumbs[] = array(
				'title' => date('d ', $__news_item['created_at'])
						   . $MONTHS[date('n', $__news_item['created_at'])]['ua']
						   . date(' Y', $__news_item['created_at'])
			);
			break;

		case 'static_page/index':
			$link = '/';
			for ($i=0; $i < count($__static_pages_array); $i++) {
				$link .= $__static_pages_array[$i]['key'] . '/';
				$breadcrumbs[] = array('title' => $__static_pages_array[$i]['breadcrumb'], 'link' => $link);
			}
			break;

		case 'error/404':
			$breadcrumbs[] = array('title' => 'Помилка 404');
			break;
	}
?>
<breadcrumbs itemscope itemtype="http://schema.org/BreadcrumbList">
	<?php
		for ($i=0; $i < count($breadcrumbs); $i++) {
			if ($i < count($breadcrumbs) - 1) {
				?>
				<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
					<a itemprop="item" href="<?= BASE_URL . $breadcrumbs[$i]['link']; ?>"><span itemprop="name"><?= $breadcrumbs[$i]['title']; ?></span></a>
					<meta itemprop="position" content="<?= $i + 1; ?>" />
				</span>&nbsp;/&nbsp;
				<?php
			} else {
				?><span class="current"><?= $breadcrumbs[$i]['title']; ?></span><?php
			}
		}
	?>
</breadcrumbs>