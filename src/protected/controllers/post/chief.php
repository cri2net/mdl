<?php
try {
    $_SESSION['chief'] = [];
    $fields = ['name' => 'Ім\'я', 'email' => 'Електронна пошта', 'text' => 'Текст повідомлення'];

    // переганяем данные в сессию, чтобы можно было их подставить обратно на форму
    foreach ($fields as $key => $value) {
        $_SESSION['chief'][$key] = trim(stripslashes($_POST[$key]));
    }
       
    // все ли поля заполнены
    foreach ($fields as $key => $title) {
        if (!$_SESSION['chief'][$key]) {
            $error = str_replace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            $_SESSION['chief']['error']['field'] = $key;
            throw new Exception($error);
        }
    }

    // корректность email
    if (!filter_var($_SESSION['chief']['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['chief']['error']['field'] = 'email';
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }

    // country - проверочное поле, оно должно быть пустым
    if ($_POST['country']) {
        throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
    }

    $data = [
        'email' => $_SESSION['chief']['email'],
        'phone' => '',
        'to' => (int)$_POST['chief_id'],
        'user_id' => (int)Authorization::getLoggedUserId(),
        'name' => $_SESSION['chief']['name'],
        'surname' => '',
        'fathername' => '',
        'timestamp' => microtime(true),
        'ip' => USER_REAL_IP,
        'subject' => '',
        'text' => $_SESSION['chief']['text']
    ];
    PDO_DB::insert($data, TABLE_PREFIX . 'feedback');

    ////////////////
    // send email //
    ////////////////
    
    $chief = PDO_DB::row_by_id(DB_TBL_CHIEF, $_POST['chief_id']);
    
    if ($chief && filter_var($chief['email'], FILTER_VALIDATE_EMAIL)) {
       
        $email = new Email();
        $email->AddReplyTo($_SESSION['chief']['email'], $_SESSION['chief']['name']);
        
        $email->send(
            [$chief['email'], "{$chief['name']} {$chief['fathername']}"],
            'КП «ГіОЦ». Нове повідомлення з сайта',
            '',
            'chief_feedback',
            [
                'username' => htmlspecialchars($_SESSION['chief']['name']),
                'email' => $_SESSION['chief']['email'],
                'text' => htmlspecialchars($_SESSION['chief']['text'])
            ]
        );
    }

    $_SESSION['chief']['status'] = true;

} catch (Exception $e) {
    $_SESSION['chief']['status'] = false;
    $_SESSION['chief']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/about/chief/';
