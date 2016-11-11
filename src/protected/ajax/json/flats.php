<?php

use cri2net\php_pdo_db\PDO_DB;

Flat::rebuildHouse($_GET['city_id'], $_GET['street_id'], $_GET['house_id']);
$arr = Flat::get($_GET['house_id'], $_GET['street_id'], $_GET['city_id']);

$flats = [];
for ($i=0; $i < count($arr); $i++) {
    $flats[] = ['label' => $arr[$i]['flat_number'], 'id' => $arr[$i]['object_id']];
}

usort($flats, 'sort_for_address');
echo json_encode($flats, JSON_UNESCAPED_UNICODE);
