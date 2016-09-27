<?php

use cri2net\php_pdo_db\PDO_DB;

try {
    $_SESSION['cabinet-settings'] = [];

    if (!$__userData) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $update = [
        'deleted' => 1,
        'deleted_message' => stripslashes($_POST['comment']),
        'deleted_timestamp' => microtime(true)
    ];

    PDO_DB::update($update, User::TABLE, $__userData['id']);
    Authorization::logout();
    Http::redirect(BASE_URL . '/');

} catch (Exception $e) {
    $_SESSION['cabinet-settings']['status'] = false;
    $_SESSION['cabinet-settings']['error']['text'] = $e->getMessage();
}
