<?php

use cri2net\php_pdo_db\PDO_DB;

try {

    $_phone = '+' . preg_replace('/[^0-9]/', '', $_POST['phone']);
    
    if (strlen($_phone) < 13) {
        throw new Exception('Телефон вказано некоректно');
    }

    $data = [
        'email'      => '',
        'phone'      => $_phone,
        'user_id'    => (int)Authorization::getLoggedUserId(),
        'name'       => @$__userData['name'] . '',
        'surname'    => @$__userData['lastname'] . '',
        'fathername' => @$__userData['fathername'] . '',
        'timestamp'  => microtime(true),
        'ip'         => USER_REAL_IP,
        'subject'    => 'Передзвоніть мені будь ласка ' . $_phone,
        'address'    => '',
        'text'       => '[Автоматично] Запит на дзвінок на телефон ' . $_phone . ' зі сторінки /services/',
    ];
    PDO_DB::insert($data, TABLE_PREFIX . 'feedback');

    $_SESSION['services-callback']['status'] = true;
} catch (Exception $e) {
    $_SESSION['services-callback']['status'] = false;
    $_SESSION['services-callback']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/services/#callback-form';
