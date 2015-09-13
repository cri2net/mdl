<?php
    try {
        $_SESSION['object-item-detailbill'] = array();
        if (!Authorization::isLogin()) {
            throw new Exception(ERROR_USER_NOT_LOGGED_IN);
        }

        $flatData = Flat::getUserFlatById($_POST['flat_id']);
        if (!$flatData) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }

        $month = (int)$_POST['month'];
        $month = max($month, 1);
        $month = min($month, 12);

        $_SESSION['object-item-detailbill']['month']   = $month;
        $_SESSION['object-item-detailbill']['year']    = (int)$_POST['year'];
        $_SESSION['object-item-detailbill']['service'] = $_POST['service']; // пока не фильтруем

    } catch (Exception $e) {
        $_SESSION['object-item']['status'] = false;
        $_SESSION['object-item']['error']['text'] = $e->getMessage();
    }

    if ($flatData['id']) {
        return BASE_URL . '/cabinet/objects/'. $flatData['id'] .'/detailbill/';
    }
    return BASE_URL . '/cabinet/objects/';
