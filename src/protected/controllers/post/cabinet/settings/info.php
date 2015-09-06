<?php
    try {
        $_SESSION['cabinet-settings'] = array();
        $_POST['mob_phone'] = '+' . preg_replace('/[^0-9]/', '', $_POST['mob_phone']);
        $fields = array('name' => 'Ім\'я', 'fathername' => 'По-батьковi', 'lastname' => 'Прiзвище', 'email' => 'Електронна пошта', 'mob_phone' => 'Телефон');
        
        $__userData = User::getUserById(Authorization::getLoggedUserId());

        if (!$__userData) {
            throw new Exception(ERROR_USER_NOT_LOGGED_IN);
        }

        $update = array();
        // переганяем данные в сессию, чтобы можно было их подставить обратно на форму
        foreach ($fields as $key => $value) {
            $update[$key] = trim(stripslashes($_POST[$key]));
        }
           
        // все ли поля заполнены
        foreach ($fields as $key => $title) {
            if (!$update[$key]) {
                $error = str_replace('{FIELD}', $title, ERROR_FIELD_EMPTY_ERROR_MSG);
                throw new Exception($error);
            }
        }

        // корректность email
        if (!filter_var($update['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
        }

        // email
        if (
            (strcmp($__userData['email'], $update['email']) !== 0)
            && (User::getUserIdByEmail($update['email']) != null)
        ) {
            throw new Exception(ERROR_EMAIL_ALREADY_EXIST);
        }

        // уникальность телефона
        if (
            (strcmp($__userData['mob_phone'], $update['mob_phone']) !== 0)
            && (User::getUserIdByPhone($update['mob_phone']) != null)
        ) {
            throw new Exception(ERROR_PHONE_ALREADY_EXIST);
        }

        PDO_DB::update($update, User::TABLE, $__userData['id']);

        $_SESSION['cabinet-settings']['status'] = true;
        $_SESSION['cabinet-settings']['text'] = 'Змiни збережено';
    } catch (Exception $e) {
        $_SESSION['cabinet-settings']['status'] = false;
        $_SESSION['cabinet-settings']['error']['text'] = $e->getMessage();
    }