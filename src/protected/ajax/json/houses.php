<?php
	$arr = House::get($_GET['street_id']);
	$houses = array();

	for ($i=0; $i < count($arr); $i++)
		$houses[] = array('label' => $arr[$i]['house_number'], 'id' => $arr[$i]['house_id']);

	echo json_encode($houses);
