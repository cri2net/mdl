<?php
	// $arr = KomDebt::get($_GET['oblect_id']);
	$debts = array();

	// for ($i=0; $i < count($arr); $i++)
	// 	$debts[] = array('label' => $arr[$i]['flat_number'], 'id' => $arr[$i]['object_id']);

	echo json_encode($debts);
