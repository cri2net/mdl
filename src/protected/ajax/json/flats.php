<?php
	$arr = Flat::get($_GET['house_id'], $_GET['street_id']);
	$flats = array();

	for ($i=0; $i < count($arr); $i++)
		$flats[] = array('label' => $arr[$i]['flat_number'], 'id' => $arr[$i]['object_id']);

	echo json_encode($flats);
