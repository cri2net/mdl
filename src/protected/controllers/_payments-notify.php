<?php
	$paysystem = basename($__route_result['values']['paysystem']);


	try
	{
		if(!in_array($paysystem, ShoppingCart::getActivePaySystems(true)))
			throw new Exception("UNKNOW PAYSYSTEM $paysystem");
		
		$file = ROOT . "/protected/conf/payments/$paysystem/$paysystem";
		if(file_exists($file . ".conf.php"))
			require_once($file . ".conf.php");

		if(file_exists($file . ".notify.php"))
			require_once($file . ".notify.php");
	}
	catch (Exception $e)
	{
		
	}
