<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $_SESSION['cabinet-settings'] = [];

    if (!$__userData) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $password = stripslashes($_POST['password']);
    $new_password = stripslashes($_POST['new_password']);
    $login = strtolower(stripslashes($_POST['login']));

    if (strcasecmp($login, $__userData['login']) !== 0) {
        if (strlen($login) < 3) {
            throw new Exception(ERROR_LOGIN_TOO_SHORT);
        } elseif (!preg_match("/^[a-z]{1}([a-z0-9._-]+)$/i", $login)) {
            throw new Exception(ERROR_LOGIN_NOT_VALID_FORMAT);
        } elseif (User::getUserByLogin($login) !== null) {
            throw new Exception(ERROR_LOGIN_ALREADY_EXIST);
        }

        PDO_DB::update(['login' => $login], User::TABLE, $__userData['id']);
        if ($_SESSION['auth_data']['column'] == 'login') {
            $_SESSION['auth_data']['login'] = $login;
        }
    }

    if (strlen($new_password) > 0) {
        
        // длина пароля
        if (mb_strlen($new_password, 'UTF-8') < 6) {
            throw new Exception(ERROR_PASSWORD_TOO_SHORT);
        }

        // проверяем текущий пароль
        $real_password = Authorization::generate_db_password($password, $__userData['password_key']);
        if (strcasecmp($real_password, $__userData['password']) !== 0) {
            throw new Exception(ERROR_CURRENT_PASSWORD);
        }
        
        // при смене пароля заодно обновляем парольную фразу
        $password_key = generateCode();

        $update = [
            'password' => Authorization::generate_db_password($new_password, $password_key),
            'password_key' => $password_key
        ];
        PDO_DB::update($update, User::TABLE, $__userData['id']);

        // перезаменим данные в сессии, чтобы не было проблем в дальшейшем
        // Возможно, для этого надо отдельную функцию писать
        Authorization::login($__userData['email'], $new_password);
    }
    
    $_SESSION['cabinet-settings']['status'] = true;
    $_SESSION['cabinet-settings']['text'] = 'Зміни збережено';
} catch (Exception $e) {
    $_SESSION['cabinet-settings']['status'] = false;
    $_SESSION['cabinet-settings']['error']['text'] = $e->getMessage();
}
