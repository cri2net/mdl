<?php
	
	switch ($__route_result['action'])
	{
		case '403':
			header("HTTP/1.1 403 Forbidden");
			break;
		
		case '404':
			header("HTTP/1.1 404 Not Found");
			require_once(ROOT . '/protected/layouts/_header.php');
			require_once(ROOT . '/protected/layouts/errors/404.php');
			require_once(ROOT . '/protected/layouts/_footer.php');
			break;
	}

	// require_once(ROOT . '/protected/layouts/_header.php');
		
	// $file = ROOT . '/protected/pages/' . $__route_result['action'] . '.php';
	// if(file_exists($file))
	// 	require_once($file);
		
	// require_once(ROOT . '/protected/layouts/_footer.php');
