<?php
// $arr = KomDebt::get($_GET['oblect_id']);
$debts = [];

// for ($i=0; $i < count($arr); $i++)
// 	$debts[] = ['label' => $arr[$i]['flat_number'], 'id' => $arr[$i]['object_id']];

echo json_encode($debts);
