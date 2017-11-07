<?php
try {
    $_SESSION['objects-auth'] = [];

    if (!isset($_POST['flat']) || !$_POST['flat']) {
        throw new Exception(ERROR_GET_FLAT);
    }

    if (in_array($_POST['flat'], $prohibided_flats)) {
        throw new Exception('Щоб додати даний об’єкт, зверніться до адміністрації сайту за адресою zvernennya@src.kiev.ua');
    }

    $flat = Flat::addFlat($_POST['flat'], $_POST['tenant']);

    if (!$flat) {
        throw new Exception(ERROR_NOT_FIND_FLAT);
    }
} catch (Exception $e) {
    $_SESSION['objects-auth']['status'] = false;
    $_SESSION['objects-auth']['error']['text'] = $e->getMessage();
}
