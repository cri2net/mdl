<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $response = [];
    $good_keys = ['OSCHADBANK_CARD_COUNT', 'TAS_FRAME_LOAD', 'TAS_FRAME_NOT_LOAD', 'TAS_FRAME_LOAD_SLOW'];

    if (!in_array($_POST['key'], $good_keys)) {
        throw new Exception("This counter's key is not permitted");
    }

    $message = $_POST['order_id'] . date(' Y.m.d H:i:s ') . $_POST['key'] . ' ' . USER_REAL_IP . ' ' . HTTP_USER_AGENT . "\r\n";
    $handle = fopen(ROOT . "/protected/logs/counters.txt", 'a+');
    fwrite($handle, $message);
    fclose($handle);

    switch ($_POST['action']) {
        case 'increment':
            $counter = PDO_DB::first(TABLE_PREFIX . 'text', "variable='{$_POST['key']}'");
            if ($counter === null) {
                $arr = [
                    'variable' => $_POST['key'],
                    'text' => 1
                ];
                PDO_DB::insert($arr, TABLE_PREFIX . 'text', true);
            } else {
                $counter = intval($counter['text']);
                $arr = ['text' => $counter + 1];
                PDO_DB::updateWithWhere($arr, TABLE_PREFIX . 'text', "variable='{$_POST['key']}'");
            }
            break;

        default:
            throw new Exception("Невідома дія");
    }

    $response['status'] = true;
    
} catch (Exception $e) {
    $response = ['status' => false, 'text' => $e->getMessage()];
}

echo json_encode($response);
