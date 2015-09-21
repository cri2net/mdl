<?php
    try {
        $_email = stripslashes($_POST['email']);
        $_SESSION['restore'] = ['email' => $_email];

        if (!filter_var($_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(ERROR_INCORRECT_EMAIL_ERROR_MSG);
        }

        $user = User::getUserByEmail($_email);
        $mail = new Email();

        if ($user !== null) {
            // пользователь найден.
            $restore_code = Authorization::generateRestoreCode($user['id']);
            $href = BASE_URL . '/cabinet/restore/' . $restore_code . '/';
            
            $mail->send(
                [$user['email'], "{$user['name']} {$user['fathername']}"],
                'Відновлення доступу',
                '',
                'restore_password',
                [
                    'username' => htmlspecialchars("{$user['name']} {$user['fathername']}"),
                    'email'    => $user['email'],
                    'href'     => $href
                ]
            );
        } else {
            // пользователь не найден
            $href = BASE_URL . '/cabinet/registration/';
            
            $mail->send(
                $_email,
                'Запит на відновлення доступу',
                '',
                'restore_password_fail',
                ['email' => $_email, 'href' => $href]
            );
        }

        $_SESSION['restore']['email_link'] = Email::getLinkToService($_email);
        $_SESSION['restore']['status'] = true;

    } catch (Exception $e) {
        $_SESSION['restore']['status'] = false;
        $_SESSION['restore']['error']['text'] = $e->getMessage();
    }
    
    return BASE_URL . '/cabinet/restore/';
