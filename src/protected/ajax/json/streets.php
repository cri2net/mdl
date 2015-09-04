<?php
    $limit = (isset($_GET['limit'])) ? ((int)$_GET['limit']) : 30;

    $arr = Street::get($_GET['request'], Street::KIEV_ID, $limit);
    $streets = array();

    for ($i=0; $i < count($arr); $i++) {
        $streets[] = array('label' => $arr[$i]['name_ua'], 'id' => $arr[$i]['street_id']);
    }

    echo json_encode($streets);
