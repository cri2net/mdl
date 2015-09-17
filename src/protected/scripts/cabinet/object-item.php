<div class="h1-line-cabinet">
    <h1 class="big-title">Об'єкти</h1>
    <div class="secure">особистий кабiнет</div>
</div>
<?php
    try {
        $user_id = Authorization::getLoggedUserId();
        $object = Flat::getUserFlatById($__route_result['values']['id']);
        $current_section = $__route_result['values']['section'];
        
        if (!$object || ($object['user_id'] != $user_id)) {
            throw new Exception(ERROR_GET_FLAT);
        }
    } catch (Exception $e) {
        ?><h2 class="big-error-message"><?= $e->getMessage(); ?></h2> <?php
        return;
    }
?>
<div class="cabinet-settings object-item object-item-<?= $current_section; ?>">
    <div class="page-tabs page-tabs-4">
        <?php
            $sections = [
                'bill'        => 'Рахунок до сплати',
                'detailbill'  => 'Історія нарахувань',
                'historybill' => 'Довідка про платежі',
                'edit'        => 'Редагувати об\'єкт',
            ];
            
            $subsections = [
                'bill'        => ['paybill', 'checkout'],
                'detailbill'  => [],
                'historybill' => [],
                'edit'        => [],
            ];

            $i = 0;
            
            foreach ($sections as $key => $value) {
                $i++;
                $current = (
                    ($current_section == $key) || in_array($current_section, $subsections[$key]));
                $class = 'tab';
                $class .= ($current) ? ' current' : '';
                $class .= ($i == count($sections)) ? ' last' : '';

                if ($current) {
                    ?><div class="<?= $class; ?>"><?= $value; ?></div><?php
                } else {
                    ?><a class="<?= $class; ?>" href="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/<?= $key; ?>/"><?= $value; ?></a><?php
                }
            }
        ?>
    </div>
    <?php
        if (isset($_SESSION['object-item']['status']) && !$_SESSION['object-item']['status']) {
            ?>
            <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
            <div class="error-description"><?= $_SESSION['object-item']['error']['text']; ?></div>
            <?php
            unset($_SESSION['object-item']['status']);
        } elseif (isset($_SESSION['object-item']['status'])) {
            ?><h2 class="big-success-message"><?= $_SESSION['object-item']['text']; ?></h2> <?php
            unset($_SESSION['object-item']);
        }

        $file = ROOT . "/protected/scripts/cabinet/object-item/$current_section.php";
        if (file_exists($file)) {
            require_once($file);
        }
    ?>
</div>
