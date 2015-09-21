<?php
    try {
        $phone = '+' . preg_replace('/[^0-9]/', '', $_POST['phone']);
        $email = stripslashes($_POST['email']);
        $password = stripslashes($_POST['password']);

        $_SESSION['login'] = [
            'email' => $email,
            'phone' => $phone,
        ];
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL) || (strlen($email) >= 3)) {
            $login = $email;
        } elseif (strlen($phone) > 7) {
            $login = $phone;
        } else {
            throw new Exception(ERROR_LOGIN_FIELDS_EMPTY);
        }

        // на форме не отрисовано, но ставим пока "запомнить меня"
        Authorization::login($login, $password, false, true);
        if (Authorization::isLogin()) {
            if (
                isset($_SERVER['HTTP_REFERER'])
                && (strpos($_SERVER['HTTP_REFERER'], BASE_URL) === 0)
                && ($_SERVER['HTTP_REFERER'] != BASE_URL . '/cabinet/login/')
            ) {
                return $_SERVER['HTTP_REFERER'];
            }
            return BASE_URL . '/cabinet/';
        }
        
        throw new Exception(ERROR_LOGIN_ERROR_MSG);

    } catch (Exception $e) {
        $_SESSION['login']['status'] = false;
        $_SESSION['login']['error']['text'] = $e->getMessage();

        if (isset($_SERVER['HTTP_REFERER']) && (strpos($_SERVER['HTTP_REFERER'], BASE_URL) === 0)) {
            return $_SERVER['HTTP_REFERER'];
        }
        return BASE_URL . '/cabinet/login/';
    }
