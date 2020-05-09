<?php
try {
    $login = stripslashes($_POST['email']);
    $password = stripslashes($_POST['password']);

    $_SESSION['login'] = [
        'login' => $login,
    ];

    if (empty($login)) {
        throw new Exception(ERROR_LOGIN_ERROR_MSG);
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
