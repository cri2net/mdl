<?php
	require_once(ROOT . '/protected/layouts/_header.php');
		
	$file = ROOT . '/protected/pages/' . $__route_result['action'] . '.php';
	if(file_exists($file))
		require_once($file);
		
	require_once(ROOT . '/protected/layouts/_footer.php');
