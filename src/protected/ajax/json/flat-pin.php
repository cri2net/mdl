<?php

use cri2net\php_pdo_db\PDO_DB;

$data = [
	'id_flat'    => (int)$_POST['id_flat'],
	'id_user'    => (int)$_SESSION['auth']['id'],
	'created_at' => microtime(true),
	'pin'        => rand(0,9).rand(0,9).rand(0,9).rand(0,9)
];

if(!Authorization::isLogin()) {
	$res = ['result' => 'error', 'msg' => 'Not authorized'];
}
else {
	PDO_DB::insert($data, TABLE_PREFIX . "flats_pin");
	
	
	try {
		$email = new Email();
		$email->changeMXToQuick();
		$res = $__userData;
		$email->send(
		    [$__userData['email'], "{$__userData['name']} {$__userData['fathername']}"],
		    'Перевірочний код',
		    '',
		    'flat-pin',
		    [
		        'username'    => htmlspecialchars("{$__userData['name']} {$__userData['fathername']}"),
		        'pin'       => $data['pin']
		    ]
		);
		$res = ['result' => 'ok', 'data' => $__userData];
	} catch(Exception $e) {
		$res = ['result' => 'error', 'msg' => $e->getMessage];
	}
}

echo json_encode($res, JSON_UNESCAPED_UNICODE);