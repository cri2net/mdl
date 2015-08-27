<breadcrumbs>
	<div xmlns:v="http://rdf.data-vocabulary.org/#">
		<?php
			switch($__route_result['controller'] . "/" . $__route_result['action']) {
				case 'page/index':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span class="current">Головна</span><?php
					break;

				case 'page/about':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span class="current">Про ГіOЦ</span><?php
					break;

				case 'page/news':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span class="current">Новини</span><?php
					break;
				case 'page/news-item':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/news/">Новини</a></span>&nbsp;/&nbsp;<span class="current">14 червня 2015</span><?php
					break;

				case 'error/404':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span class="current">Помилка 404</span><?php
					break;
			}
		?>
	</div>
</breadcrumbs>