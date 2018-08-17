<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $_SESSION['cabinet-settings'] = [];
    $_POST['mob_phone'] = '+' . preg_replace('/[^0-9]/', '', $_POST['mob_phone']);
    $fields = ['name' => 'Ім’я', 'fathername' => 'По батькові', 'lastname' => 'Прізвище', 'email' => 'Електронна пошта', 'mob_phone' => 'Телефон'];
    
    if (!$__userData) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $update = [];
    // переганяем данные в сессию, чтобы можно было их подставить обратно на форму
    foreach ($fields as $key => $value) {
        $update[$key] = trim(stripslashes($_POST[$key]));
    }
       
    // все ли поля заполнены
    foreach ($fields as $key => $title) {
        if (!$update[$key] && ($key != 'fathername')) {
            $error = str_replace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            throw new Exception($error);
        }
    }

    // корректность email
    if (!filter_var($update['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }

    // email
    if (strcmp($__userData['email'], $update['email']) !== 0) {
        if (User::getUserIdByEmail($update['email']) != null) {
            throw new Exception(ERROR_EMAIL_ALREADY_EXIST);
        } else {
            $update['broken_email'] = 0;
            $update['verified_email'] = 0;
        }

        if ($_SESSION['auth_data']['column'] == 'email') {
            $_SESSION['auth_data']['login'] = $update['email'];
        }
    }

    // уникальность телефона
    if (strcmp($__userData['mob_phone'], $update['mob_phone']) !== 0) {
        if (User::getUserIdByPhone($update['mob_phone']) != null) {
            throw new Exception(ERROR_PHONE_ALREADY_EXIST);
        } else {
            $update['verified_phone'] = 0;
        }

        if ($_SESSION['auth_data']['column'] == 'mob_phone') {
            $_SESSION['auth_data']['login'] = $update['mob_phone'];
        }
    }

    PDO_DB::update($update, User::TABLE, $__userData['id']);

    $_SESSION['cabinet-settings']['status'] = true;
    $_SESSION['cabinet-settings']['text'] = 'Зміни збережено';
} catch (Exception $e) {
    $_SESSION['cabinet-settings']['status'] = false;
    $_SESSION['cabinet-settings']['error']['text'] = $e->getMessage();
}
