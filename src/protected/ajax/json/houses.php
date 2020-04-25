<?php

use cri2net\php_pdo_db\PDO_DB;

$street = PDO_DB::row_by_id(Street::TABLE, $_GET['street_id'], 'street_id');
$arr = House::get($_GET['street_id']);

$houses = [];
for ($i=0; $i < count($arr); $i++) {
    $houses[] = ['label' => $arr[$i]['house_number'], 'id' => $arr[$i]['house_id']];
}

usort($houses, 'sort_for_address');
echo json_encode($houses, JSON_UNESCAPED_UNICODE);
