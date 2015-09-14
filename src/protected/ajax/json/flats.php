<?php
    $arr = Flat::get($_GET['house_id'], $_GET['street_id']);
    $flats = [];

    for ($i=0; $i < count($arr); $i++) {
        $flats[] = ['label' => $arr[$i]['flat_number'], 'id' => $arr[$i]['object_id']];
    }

    echo json_encode($flats);
