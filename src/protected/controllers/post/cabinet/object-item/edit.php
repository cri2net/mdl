<?php
try {
    if (!Authorization::isLogin()) {
        throw new Exception(ERROR_USER_NOT_LOGGED_IN);
    }

    $flatData = Flat::getUserFlatById($_POST['flat_id']);
    if (!$flatData) {
        throw new Exception(ERROR_NOT_FIND_FLAT);
    }
    
    if (isset($_POST['delete_object']) && $_POST['delete_object']) {
        Flat::removeUserFlat($flatData['id']);
        return BASE_URL . '/cabinet/objects/';
    }

    $update = [
        'notify' => intval(isset($_POST['notify_house']) && $_POST['notify_house'])
    ];
    Flat::renameUserFlat($flatData['id'], trim(stripslashes($_POST['object-title'])));
    PDO_DB::update($update, Flat::USER_FLATS_TABLE, $flatData['id']);


    $_SESSION['object-item']['status'] = true;
    $_SESSION['object-item']['text'] = 'Дані збережено';

} catch (Exception $e) {
    $_SESSION['object-item']['status'] = false;
    $_SESSION['object-item']['error']['text'] = $e->getMessage();
}

if ($flatData['id']) {
    return BASE_URL . '/cabinet/objects/'. $flatData['id'] .'/edit/';
}
return BASE_URL . '/cabinet/objects/';
