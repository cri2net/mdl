<?php

use cri2net\php_pdo_db\PDO_DB;

$street = PDO_DB::row_by_id(Street::TABLE, $_GET['street_id'], 'street_id');
House::rebuildStreet($street['city_id'], $_GET['street_id']);
$arr = House::get($_GET['street_id']);

// делаем умную сортировку.
// правильный порядок: 1, 2, 10
// НЕправильный порядок: 1, 10, 2

$max = 0;
for ($i=0; $i < count($arr); $i++) {
    $max = max($max, strlen($arr[$i]['house_number']));
}

$keys = [];
for ($i=0; $i < count($arr); $i++) {
    $keys[$i] = str_pad($arr[$i]['house_number'], $max, '0', STR_PAD_LEFT);
}

array_multisort($keys, SORT_ASC, $arr);

$houses = [];
for ($i=0; $i < count($arr); $i++) {
    $houses[] = ['label' => $arr[$i]['house_number'], 'id' => $arr[$i]['house_id']];
}

echo json_encode($houses, JSON_UNESCAPED_UNICODE);
