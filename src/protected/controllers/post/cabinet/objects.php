<?php
use cri2net\php_pdo_db\PDO_DB;

try {
    $_SESSION['objects-auth'] = [];

    if (!isset($_POST['flat']) || !$_POST['flat']) {
        throw new Exception(ERROR_GET_FLAT);
    }
    $_idFlat = (double)$_POST['flat'];

    if (in_array($_POST['flat'], $prohibided_flats)) {
        throw new Exception('Щоб додати даний об’єкт, зверніться до адміністрації сайту за адресою Support.my@kievcity.gov.ua');
    }

    $verify_type = Flat::getVerifyType($_idFlat);

    if ($verify_type == 'pin') {
        $pins = PDO_DB::table_list(TABLE_PREFIX . 'flats_pin', "id_user={$__userData['id']} && id_flat='$_idFlat'", "created_at DESC", 1);
        $pin = $pins[0];
        if ($pin['pin'] != $_POST['pin']) {
            throw new Exception(ERROR_FLAT_PIN);
        }
    } else {
        $auth_key = stripslashes($_POST['auth_key']);

        if (!Flat::verify_auth_key($auth_key, $_POST['flat'])) {
            throw new Exception(ERROR_FLAT_INVALID_AUTH_KEY);
        }
    }

    $flat = Flat::addFlat($_POST['flat'], $_POST['tenant']);

    if (!$flat) {
        throw new Exception(ERROR_NOT_FIND_FLAT);
    }
} catch (Exception $e) {
    $_SESSION['objects-auth']['status'] = false;
    $_SESSION['objects-auth']['error']['text'] = $e->getMessage();
}
