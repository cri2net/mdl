<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    
    $_SESSION['services_request']['status'] = true;

    $mail = new Email();
    $checked_services = [];

    foreach ($SERVICES as $key => $s) {
        if ($_POST['service'][$key]) {
            $checked_services[] = $s;
        }
    }

    $mail->send(
        'service@src.kiev.ua',
        'Нове питання до фахівця',
        '',
        'services_request',
        [
            'fio'       => htmlspecialchars($_POST['fio']),
            'address'   => htmlspecialchars($_POST['address']),
            'phones'    => htmlspecialchars($_POST['phones']),
            'worktypes' => nl2br(htmlspecialchars($_POST['worktypes'])),
            'workadd'   => nl2br(htmlspecialchars($_POST['workadd'])),
            'services'  => implode(', ', $checked_services),
        ]
    );

} catch (Exception $e) {
    $_SESSION['services_request']['status'] = false;
    $_SESSION['services_request']['error']['text'] = $e->getMessage();
}
