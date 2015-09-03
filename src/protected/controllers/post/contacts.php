<?php
    try {
        $_SESSION['contacts'] = array();
        $fields = array('name' => 'Ім\'я', 'email' => 'Електронна пошта', 'text' => 'Текст повiдомлення');

        // переганяем данные в сессию, чтобы можно было их подставить обратно на форму
        foreach ($fields as $key => $value) {
            $_SESSION['contacts'][$key] = trim(stripslashes($_POST[$key]));
        }
           
        // все ли поля заполнены
        foreach ($fields as $key => $title) {
            if (!$_SESSION['contacts'][$key]) {
                $error = str_replace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
                $_SESSION['contacts']['error']['field'] = $key;
                throw new Exception($error);
            }
        }

        // корректность email
        if (!filter_var($_SESSION['contacts']['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['contacts']['error']['field'] = 'email';
            throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
        }

        // country - проверочное поле, оно должно быть пустым
        if ($_POST['country']) {
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        }

        $data = array(
            'email' => $_SESSION['contacts']['email'],
            'phone' => '',
            'user_id' => (int)Authorization::getLoggedUserId(),
            'name' => $_SESSION['contacts']['name'],
            'surname' => '',
            'fathername' => '',
            'timestamp' => microtime(true),
            'ip' => USER_REAL_IP,
            'subject' => '',
            'text' => $_SESSION['contacts']['text']
        );
        PDO_DB::insert($data, TABLE_PREFIX . 'feedback');

        $_SESSION['contacts']['status'] = true;
    } catch (Exception $e) {
        $_SESSION['contacts']['status'] = false;
        $_SESSION['contacts']['error']['text'] = $e->getMessage();
    }