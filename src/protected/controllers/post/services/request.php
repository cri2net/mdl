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
            'services'  => ,
        ]
    );

    $data = [
        'email'      => '',
        'phone'      => $_POST['phones'],
        'user_id'    => (int)Authorization::getLoggedUserId(),
        'name'       => $_POST['fio'],
        'surname'    => '',
        'fathername' => '',
        'timestamp'  => microtime(true),
        'ip'         => USER_REAL_IP,
        'subject'    => 'Нове питання до фахівця',
        'address'    => $_POST['address'],
        'text'       => "[Автоматично] Нове питання до фахівця:\r\n\r\nПІБ: {{fio}}\r\n Адреса: {{address}}\r\n Телефони: {{phones}}\r\n Вид робіт: {{worktypes}}\r\n\r\n Додаткові роботи/матеріали, інше: {{workadd}}\r\n\r\n Питання стосовно: {{services}}",
    ];

    $data['text'] = str_replace('{{fio}}', htmlspecialchars($_POST['fio']), $data['text']);
    $data['text'] = str_replace('{{address}}', htmlspecialchars($_POST['address']), $data['text']);
    $data['text'] = str_replace('{{phones}}', htmlspecialchars($_POST['phones']), $data['text']);
    $data['text'] = str_replace('{{worktypes}}', htmlspecialchars($_POST['worktypes']), $data['text']);
    $data['text'] = str_replace('{{workadd}}', htmlspecialchars($_POST['workadd']), $data['text']);
    $data['text'] = str_replace('{{services}}', implode(', ', $checked_services), $data['text']);
    
    PDO_DB::insert($data, TABLE_PREFIX . 'feedback');

} catch (Exception $e) {
    $_SESSION['services_request']['status'] = false;
    $_SESSION['services_request']['error']['text'] = $e->getMessage();
}
