<div class="breadcrumbs">
	<div xmlns:v="http://rdf.data-vocabulary.org/#">
		<?php
			switch($__route_result['action']) {
				case 'index':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span class="current">Головна</span><?php
					break;

				case 'about':
					?><span typeof="v:Breadcrumb"><a rel="v:url" property="v:title" href="<?= BASE_URL; ?>/">ГіОЦ</a></span>&nbsp;/&nbsp;<span class="current">Про ГіOЦ</span><?php
					break;
			}
		?>
	</div>
</div>