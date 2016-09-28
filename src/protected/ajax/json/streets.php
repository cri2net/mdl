<?php
$limit = (isset($_GET['limit'])) ? ((int)$_GET['limit']) : 30;

$arr = Street::get($_GET['request'], $_GET['city_id'], $limit);
$streets = [];

for ($i=0; $i < count($arr); $i++) {
    $streets[] = ['label' => $arr[$i]['name_ru'], 'id' => $arr[$i]['street_id']];
}

echo json_encode($streets, JSON_UNESCAPED_UNICODE);
