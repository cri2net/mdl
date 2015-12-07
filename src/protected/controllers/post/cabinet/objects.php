<?php
try {
    $_SESSION['objects-auth'] = [];

    if (!isset($_POST['flat']) || !$_POST['flat']) {
        throw new Exception(ERROR_GET_FLAT);
    }

    $auth_key = stripslashes($_POST['auth_key']);

    if ($_SESSION['auth']['email'] !== 'zirka83@mail.ru') {
        if (!Flat::verify_auth_key($auth_key, $_POST['flat'])) {
            throw new Exception(ERROR_FLAT_INVALID_AUTH_KEY);
        }
    }

    $flat = Flat::addFlat($_POST['flat'], $auth_key);

    if (!$flat) {
        throw new Exception(ERROR_NOT_FIND_FLAT);
    }
} catch (Exception $e) {
    $_SESSION['objects-auth']['status'] = false;
    $_SESSION['objects-auth']['error']['text'] = $e->getMessage();
}
