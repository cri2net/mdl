<?php
    try {
        $_SESSION['objects-auth'] = array();

        if (!isset($_POST['flat']) || !$_POST['flat']) {
            throw new Exception(ERROR_GET_FLAT);
        }

        $flat = Flat::addFlat($_POST['flat']);

        if (!$flat) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }
    } catch (Exception $e) {
        $_SESSION['objects-auth']['status'] = false;
        $_SESSION['objects-auth']['error']['text'] = $e->getMessage();
    }
