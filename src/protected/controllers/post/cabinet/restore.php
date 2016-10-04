<?php
try {
    $email = stripslashes($_POST['email']);
    $phone = '+' . preg_replace('/[^0-9]/', '', $_POST['phone']);
    $_SESSION['restore'] = [
        'email' => $email,
        'success_text' => 'Перевірте свою поштову скриньку, ми відправили Вам лист з подальшими інструкціями.'
    ];

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $user = User::getUserByEmail($email);
    } elseif (strlen($phone) > 7) {
        $user = User::getUserByPhone($phone);
    } elseif (strlen($email) >= 3) {
        $user = User::getUserByLogin($email);
    } else {
        throw new Exception(ERROR_RESTORE_FIELDS_EMPTY);
    }

    $mail = new Email();
    $mail->changeMXToQuick();

    if ($user !== null) {
        // пользователь найден.
        $restore_code = Authorization::generateUserCode($user['id']);
        $href = EXT_BASE_URL . '/cabinet/restore/' . $restore_code . '/';
        
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // мы нашли пользователя, но не по email. Значит, надо показать человеку email, на который мы отправили письмо
            $hidden_email = explode('@', $user['email']);

            if (strlen($hidden_email[0]) <= 4) {
                $hidden_email = $user['email'];
            } else {
                $stars = '';

                while (strlen($stars) < strlen($hidden_email[0]) - 4) {
                    $stars .= '*';
                }

                $hidden_email = substr($hidden_email[0], 0, 4) . $stars . '@' . $hidden_email[1];
            }

            $_SESSION['restore']['success_text'] = 'Перевірте свою поштову скриньку, ми відправили Вам лист на ' . $hidden_email . ' з подальшими інструкціями.';
        }

    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // пользователь не найден, но у нас есть его email
        $href = EXT_BASE_URL . '/cabinet/registration/';
        
        $mail->send(
            $email,
            'Запит на відновлення доступу',
            '',
            'restore_password_fail',
            ['email' => $email, 'href' => $href]
        );
    } else {
        throw new Exception(ERROR_RESTORE_CANNOT_FIND_USER);
    }

    $_SESSION['restore']['email_link'] = Email::getLinkToService($email);
    $_SESSION['restore']['status'] = true;

} catch (Exception $e) {
    $_SESSION['restore']['status'] = false;
    $_SESSION['restore']['error']['text'] = $e->getMessage();
}

return BASE_URL . '/cabinet/restore/';
