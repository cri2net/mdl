<div class="menu">
	<?php
		$main_menu = getMenu('main');
		$have_main_submenu = array();
	
		for ($i=0; $i<count($main_menu); $i++) {
			
			$have_submenu = (count($main_menu[$i]['submenu']) > 0);
			$item_url = str_replace('{SITE_URL}', BASE_URL, $main_menu[$i]['link']);
			
			if ($item_url == '') {
				$item_url = '#';
			}
			
			if (!$have_submenu) {
				?> <div class="item"><a href="<?= $item_url; ?>"><?= htmlspecialchars($main_menu[$i]['title']); ?></a></div> <?php
			} else {
				$have_main_submenu[] = $main_menu[$i]['id'];
				?>
				<div class="item with-sub-menu" id="header_top_item_<?= $main_menu[$i]['id']; ?>"><a href="<?= $item_url; ?>" onmouseover="show_header_submenu('<?= $main_menu[$i]['id']; ?>'); return false;"><?= htmlspecialchars($main_menu[$i]['title']); ?></a>
					<div class="submenu" id="header_submenu_<?= $main_menu[$i]['id']; ?>">
						<?php
							for ($j=0; $j<count($main_menu[$i]['submenu']); $j++) {
								$item_url = str_replace('{SITE_URL}', BASE_URL, $main_menu[$i]['submenu'][$j]['link']);
								?><div class="subitem"><a href="<?= $item_url; ?>"><?= htmlspecialchars($main_menu[$i]['submenu'][$j]['title']); ?></a></div><?php
							}
						?>
					</div>
					<div id="header_down_1" class="down"></div>
				</div>
				<?php
			}
		}
	?>
	<script type="text/javascript">
		var have_main_submenu_item = <?= json_encode($have_main_submenu); ?>;
	</script>
	<div class="item item-right forum"><a href="<?= BASE_URL; ?>/forum/">Форум</a></div>
</div>
