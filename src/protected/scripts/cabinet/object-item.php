<body>
<?php

define('NAVBAR_FOR_OBJECT_ITEM', true);

try {
    $user_id = Authorization::getLoggedUserId();
    $object = Flat::getUserFlatById($__route_result['values']['id']);
    $current_section = $__route_result['values']['section'];
    
    if (!$object || ($object['user_id'] != $user_id)) {
        throw new Exception(ERROR_GET_FLAT);
    }

    $debt = new KomDebt();
    
    //require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');

    if (isset($_SESSION['object-item']['status']) && !$_SESSION['object-item']['status']) {
        
        unset($_SESSION['object-item']['status']);
        throw new Exception($_SESSION['object-item']['error']['text']);
    }

} catch (Exception $e) {
    ?>
    <div class="container">
        <content>
            <div class="text">
                <h1><?= $e->getMessage(); ?></h1>
            </div>
        </content>
    </div>
    <?php
    return;
}

?>
<div class="container-fluid">
    <content>
<?php
require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');

$file = PROTECTED_DIR . "/scripts/cabinet/object-item/$current_section.php";
if (file_exists($file)) {
    require_once($file);
}
?>
    </content>
</div>
