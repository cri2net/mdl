<?php
    $insert = array(
        'url' => $_POST['url'],
        'raw_data' => json_encode($_POST),
        'timestamp' => microtime(true)
    );

    PDO_DB::insert($insert, TABLE_PREFIX . 'text_errors');
