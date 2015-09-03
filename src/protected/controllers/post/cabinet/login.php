<?php
    try {
        $phone = '+' . preg_replace('/[^0-9]/', '', $_POST['phone']);
        $email = stripslashes($_POST['email']);
        $password = stripslashes($_POST['password']);

        $_SESSION['login'] = array(
            'email' => $email,
            'phone' => $phone,
        );
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $login = $email;
        } elseif (strlen($phone) > 7) {
            $login = $phone;
        } else {
            throw new Exception(ERROR_LOGIN_FIELDS_EMPTY);
        }

        // на форме не отрисовано, но ставим пока "запомнить меня"
        Authorization::login($login, $password, false, true);
        if (Authorization::isLogin()) {
            return BASE_URL . '/cabinet/';
        }
        
        throw new Exception(ERROR_LOGIN_ERROR_MSG);

    } catch (Exception $e) {
        $_SESSION['login']['status'] = false;
        $_SESSION['login']['error']['text'] = $e->getMessage();
    }
