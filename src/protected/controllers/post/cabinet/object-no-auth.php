<?php
    try {
        $_SESSION['objects'] = [];

        // country - проверочное поле, оно должно быть пустым
        if ($_POST['country']) {
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        }

        if (!isset($_POST['flat']) || !$_POST['flat']) {
            throw new Exception(ERROR_GET_FLAT);
        }

        $flat = Flat::getFlatById($_POST['flat']);
        $auth_key = stripslashes($_POST['auth_key']);

        if (!$flat) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }

        if (!Flat::verify_auth_key($auth_key, $_POST['flat'])) {
            throw new Exception(ERROR_FLAT_INVALID_AUTH_KEY);
        }

        $_SESSION['after_register']['add_object'] = $flat;
        $_SESSION['after_register']['add_object']['auth_key'] = $auth_key;
        $_SESSION['registration']['show_message'] = [
            'type' => 'success',
            'text' => 'Об\'єкт буде у вашому аккаунті після реєстрації',
        ];
        
        return BASE_URL . '/cabinet/registration/';
        
    } catch (Exception $e) {
        $_SESSION['objects']['status'] = false;
        $_SESSION['objects']['error']['text'] = $e->getMessage();
        return BASE_URL . '/cabinet/objects/';
    }
