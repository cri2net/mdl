<?php
$arr = Flat::get($_GET['house_id'], $_GET['street_id']);

// делаем умную сортировку.
// правильный порядок: 1, 2, 10
// НЕправильный порядок: 1, 10, 2

$max = 0;
for ($i=0; $i < count($arr); $i++) {
    $max = max($max, strlen($arr[$i]['flat_number']));
}

$keys = [];
for ($i=0; $i < count($arr); $i++) {
    $keys[$i] = str_pad($arr[$i]['flat_number'], $max, '0', STR_PAD_LEFT);
}

array_multisort($keys, SORT_ASC, $arr);

$flats = [];
for ($i=0; $i < count($arr); $i++) {
    $flats[] = ['label' => $arr[$i]['flat_number'], 'id' => $arr[$i]['object_id']];
}

echo json_encode($flats);
