<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $_SESSION['feedback'] = [];
    $fields = [
        'surname'     => 'Прізвище',
        'name'        => 'Ім’я',
        'fathername'  => 'По-батькові',
        'region'      => 'Район',
        // 'address'     => 'Адреса',
        'email'       => 'Електронна пошта',
        'id_theme'    => 'Напрямок питання',
        'text'        => 'Суть питання',
        'street_type' => 'Тип',
        'street'      => 'Назва вулиці',
        'house'       => 'Будинок',
        'flat'        => 'Квартира',
        'phone'       => 'Телефон',
    ];

    // переганяем данные в сессию, чтобы можно было их подставить обратно на форму
    foreach ($fields as $key => $value) {
        $_SESSION['feedback'][$key] = trim(stripslashes($_POST[$key]));
    }

    $street_types = [
        'street' => 'вулиця',
        'blvd'   => 'бульвар',
        'ave'    => 'проспект',
        'lane'   => 'провулок',
    ];
       
    // все ли поля заполнены
    foreach ($fields as $key => $title) {
        if (!$_SESSION['feedback'][$key]) {
            $error = str_replace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
            $_SESSION['feedback']['error']['field'] = $key;
            throw new Exception($error);
        }
    }

    // корректность email
    if (!filter_var($_SESSION['feedback']['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['feedback']['error']['field'] = 'email';
        throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
    }

    $address = "{$street_types[$_SESSION['feedback']['street_type']]} {$_SESSION['feedback']['street']} буд. {$_SESSION['feedback']['house']} кв. {$_SESSION['feedback']['flat']}";
    $theme = PDO_DB::row_by_id(TABLE_PREFIX . 'dict_feedback_themes', $_SESSION['feedback']['id_theme']);

    $data = [
        'email'      => $_SESSION['feedback']['email'],
        'phone'      => $_SESSION['feedback']['phone'],
        'user_id'    => (int)Authorization::getLoggedUserId(),
        'name'       => $_SESSION['feedback']['name'],
        'surname'    => $_SESSION['feedback']['surname'],
        'fathername' => $_SESSION['feedback']['fathername'],
        'timestamp'  => microtime(true),
        'ip'         => USER_REAL_IP,
        'subject'    => $theme['title'],
        'address'    => $address,
        'text'       => $_SESSION['feedback']['text'],
    ];
    PDO_DB::insert($data, TABLE_PREFIX . 'feedback');

    $_SESSION['feedback']['status'] = true;
} catch (Exception $e) {
    $_SESSION['feedback']['status'] = false;
    $_SESSION['feedback']['error']['text'] = $e->getMessage();
}
