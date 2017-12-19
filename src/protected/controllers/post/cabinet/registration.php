<?php
try {
    $_SESSION['registration'] = [];
    $_POST['phone'] = '+' . preg_replace('/[^0-9]/', '', $_POST['phone']);
    $fields = ['name' => 'Ім’я', 'fathername' => 'По батькові', 'lastname' => 'Прізвище', 'email' => 'Електронна пошта', 'phone' => 'Телефон', 'password' => 'Пароль'];
    

    // переганяем данные в сессию, чтобы можно было их подставить обратно на форму
    foreach ($fields as $key => $value) {
        $_SESSION['registration'][$key] = trim(stripslashes($_POST[$key]));
    }

    if (in_array($_POST['email'], $banned_user)) {
        throw new Exception('Аккаунт користувача заблоковано');
    }
       
    // все ли поля заполнены
    foreach ($fields as $key => $title) {
        if (!$_SESSION['registration'][$key] && ($key != 'fathername')) {
            $error = str_replace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            $_SESSION['registration']['error']['field'] = $key;
            throw new Exception($error);
        }
    }

    // корректность email
    if (!filter_var($_SESSION['registration']['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['registration']['error']['field'] = 'email';
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }

    // длина пароля
    if (mb_strlen($_SESSION['registration']['password'], 'UTF-8') < 6) {
        $_SESSION['registration']['error']['field'] = 'password';
        throw new Exception(ERROR_PASSWORD_TOO_SHORT);
    }

    // уникальность email
    if (User::getUserIdByEmail($_SESSION['registration']['email']) != null) {
        $_SESSION['registration']['error']['field'] = 'email';
        throw new Exception(ERROR_EMAIL_ALREADY_EXIST);
    }

    // уникальность телефона
    if (User::getUserIdByPhone($_SESSION['registration']['phone']) != null) {
        $_SESSION['registration']['error']['field'] = 'phone';
        throw new Exception(ERROR_PHONE_ALREADY_EXIST);
    }
    
    // country - проверочное поле, оно должно быть пустым
    if ($_POST['country']) {
        throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
    }

    $user_id = User::registration($_SESSION['registration']);
    Authorization::login($_SESSION['registration']['email'], $_SESSION['registration']['password']);

    return BASE_URL . '/cabinet/objects/';

} catch (Exception $e) {
    $_SESSION['registration']['status'] = false;
    $_SESSION['registration']['error']['text'] = $e->getMessage();
}
