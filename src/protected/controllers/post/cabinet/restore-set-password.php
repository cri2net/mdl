<?php
    try {
        $restore_code = stripslashes($_POST['code']);
        $record = Authorization::verifyRestoreCode($restore_code);
        $user = User::getUserById($record['user_id']);

        $_password = stripslashes($_POST['new_password']);

        // длина пароля
        if (mb_strlen($_password, 'UTF-8') < 6) {
            throw new Exception(ERROR_PASSWORD_TOO_SHORT);
        }

        // при смене пароля заодно обновляем парольную фразу
        $password_key = generateCode();
        $update = [
            'password' => Authorization::generate_db_password($_password, $password_key),
            'password_key' => $password_key
        ];

        PDO_DB::update($update, User::TABLE, $user['id']);
        Authorization::unsetRestoreCode($record['id']);
        Authorization::login($user['email'], $_password);
        $_SESSION['restore-secont-step']['status'] = true;
    } catch (Exception $e) {
        $_SESSION['restore-secont-step']['status'] = false;
        $_SESSION['restore-secont-step']['error']['text'] = $e->getMessage();
    }
    
    return BASE_URL . '/cabinet/restore/' . $restore_code . '/';
