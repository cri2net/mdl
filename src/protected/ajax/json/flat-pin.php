<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    $res = ['result' => 'ok'];

    if (!Authorization::isLogin()) {
        throw new Exception('Not authorized');
    }

    $data = [
        'id_flat'    => $_POST['id_flat'],
        'id_user'    => Authorization::getLoggedUserId(),
        'created_at' => microtime(true),
        'pin'        => rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9),
    ];

    PDO_DB::insert($data, TABLE_PREFIX . "flats_pin");

    $email = new Email();
    $email->changeMXToQuick();
    $email->send(
        [$__userData['email'], "{$__userData['name']} {$__userData['fathername']}"],
        'Перевірочний код',
        '',
        'flat-pin',
        [
            'username' => htmlspecialchars("{$__userData['name']} {$__userData['fathername']}"),
            'pin'      => $data['pin'],
        ]
    );
    
} catch (Exception $e) {
    $res = ['result' => 'error', 'msg' => $e->getMessage];
}

echo json_encode($res, JSON_UNESCAPED_UNICODE);
