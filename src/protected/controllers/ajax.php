<?php
	$action = basename($__route_result['values']['action']);
	$file = ROOT . '/protected/ajax/' . $__route_result['action'] . '/' . $action. '.php';
	
	switch ($__route_result['action'])
	{
		case 'json':
		case 'xml':
			header('Content-Type: text/' . $__route_result['action']);
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // дата в прошлом
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // всегда модифицируется
			header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");                                    // HTTP/1.0

			if($__route_result['action'] == 'xml')
			{
				echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
				echo '<response>';
			}
			break;
	}

	if(file_exists($file))
		require_once($file);
		

	switch ($__route_result['action'])
	{
		case 'xml':
			echo '</response>';
			break;
	}
