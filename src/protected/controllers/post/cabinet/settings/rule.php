<?php
    try {
        $_SESSION['cabinet-settings'] = [];
        $__userData = User::getUserById(Authorization::getLoggedUserId());

        if (!$__userData) {
            throw new Exception(ERROR_USER_NOT_LOGGED_IN);
        }

        $password = stripslashes($_POST['password']);
        $new_password = stripslashes($_POST['new_password']);

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
        $_SESSION['cabinet-settings']['text'] = 'Змiни збережено';
    } catch (Exception $e) {
        $_SESSION['cabinet-settings']['status'] = false;
        $_SESSION['cabinet-settings']['error']['text'] = $e->getMessage();
    }
