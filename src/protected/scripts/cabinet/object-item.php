<?php
    define('NAVBAR_FOR_OBJECT_ITEM', true);
    
    $user_id = Authorization::getLoggedUserId();
    $current_section = $__route_result['values']['section'];
    $object = Flat::getUserFlatById($__route_result['values']['id']);
    if ($object['user_id'] != $user_id) {
        $object = null;
    }
?>
<content>
    <?php
        require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
        require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');

        try {
            if (!$object) {
                throw new Exception(ERROR_GET_FLAT);
            }
            
            $debt = new KomDebt();
            
            if (isset($_SESSION['object-item']['status']) && !$_SESSION['object-item']['status']) {
                unset($_SESSION['object-item']['status']);
                throw new Exception($_SESSION['object-item']['error']['text']);
            }
        } catch (Exception $e) {
            ?>
            <content>
                <div class="text">
                    <h2 class="big-error-message"><?= $e->getMessage(); ?></h2>
                </div>
            </content>
            <?php
            return;
        }

        $file = PROTECTED_DIR . "/scripts/cabinet/object-item/$current_section.php";
        if (file_exists($file)) {
            require_once($file);
        }
    ?>
</content>
