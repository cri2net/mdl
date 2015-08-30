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

		case 'page/contacts':
			$breadcrumbs[] = array('title' => 'Контакти');
			break;

		case 'page/news':
			$breadcrumbs[] = array('title' => 'Новини');
			break;
		case 'page/news-item':
			$breadcrumbs[] = array('title' => 'Новини', 'link' => '/news/');
			$breadcrumbs[] = array('title' => '14 червня 2015');
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