<div class="h1-line-cabinet">
    <h1 class="big-title">Об'єкти</h1>
    <div class="secure">особистий кабiнет</div>
</div>
<?php
    if (isset($_SESSION['objects-auth']['status']) && !$_SESSION['objects-auth']['status']) {
        ?>
        <h2 class="big-error-message">Під час виконання запиту виникла помилка:</h2>
        <div class="error-description"><?= $_SESSION['objects-auth']['error']['text']; ?></div>
        <?php
        unset($_SESSION['objects-auth']['status']);
    }

    try {
        $user_id = Authorization::getLoggedUserId();
        $houses = Flat::getUserFlats($user_id, true);
        $debt = new KomDebt();
        
        for ($i=0; $i < count($houses); $i++) {

            $debtData = $debt->getData($houses[$i]['flat_id'], null, 0, $haveDataTime);
            $dateBegin = date('1.m.Y', $haveDataTime);

            $houses[$i]['debt_sum'] = $debtData['full_dept'];
            
            $houses[$i]['on_this_month'] = $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small'];
            $houses[$i]['date'] = $debtData['date'];

            $oplat = $debt->getPayOnThisMonth($houses[$i]['flat_id'], $dateBegin);

            $tmp_oplat = (double)str_replace(',', '.', $oplat);
            $tmp_debt_summ = (double)str_replace(',', '.', $houses[$i]['debt_sum']);

            $houses[$i]['payed'] = (($tmp_oplat > 0) && ($tmp_oplat >= $tmp_debt_summ))?'bbox-green':'';
            $houses[$i]['oplat_this_month'] = $oplat;
            $houses[$i]['oplat_this_month_str'] = substr($oplat, 0, strlen($oplat) - 3);
            $houses[$i]['oplat_this_month_str'] .= '<span class="small">'.substr($oplat, strlen($oplat) - 3).' <span class="currency">грн</span></span>';

            $houses[$i]['debt_sum_str'] = '<span class="right">'.substr($houses[$i]['debt_sum'], 0, strlen($houses[$i]['debt_sum']) - 3);
            $houses[$i]['debt_sum_str'] .= '<span class="small">'.substr($houses[$i]['debt_sum'], strlen($houses[$i]['debt_sum']) - 3).' <span class="currency">грн</span></span>';
            $houses[$i]['debt_sum_str'] .= '</span>';
            $houses[$i]['icon'] = ($houses[$i]['kvartira'] > 0) ? 'flat' : '';
        }
    } catch (Exception $e) {
        ?><h2 class="big-error-message"><?= $e->getMessage(); ?></h2> <?php
        return;
    }
?>
<div class="cabinet-objects">
    <?php
        if (count($houses) > 0) {
            ?>
            <div class="houses_line">
                <?php
                    for ($i=0; $i < count($houses); $i++) {
                        $house = $houses[$i];

                        if (($i % 2 == 0) && ($i > 0)) {
                            ?></div><div class="houses_line"><?php
                        }

                        ?>
                        <div class="house_item <?= $house['payed']; ?> <?= $house['icon']; ?>">
                            <div class="payed-icon"></div>
                            <div class="title">
                                <div class="icon"></div>
                                <?php
                                    if ($house['title']) {
                                        echo htmlspecialchars($house['title']);
                                        ?>
                                        <div class="address"><?= $house['address']; ?></div>
                                        <?php
                                    } else {
                                        $street_name = ($house['street_name'] !== $house['street_name_full'])
                                            ? $street_name = '<span title="'. $house['street_name_full'] .'">' . $house['street_name'] . '</span>'
                                            : $house['street_name'];
                                        ?>
                                        Київ, <br> <?= $street_name; ?> <br> <?= $house['detail_address']['house']; ?> кв. <?= $house['detail_address']['flat']; ?>
                                        <?php
                                    }

                                    if (!$house['error']) {
                                        ?>
                                        <div class="bydate">рахунок за <?= $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small']; ?> <?= date('Y', $debtData['timestamp']); ?> року</div>
                                        <?php
                                    }
                                ?>
                            </div>
                            <?php
                                if ($house['error']) {
                                    ?>
                                    <div class="values align-center">
                                        <b style="color:#900;">Виникла тимчасова помилка</b>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div class="values">
                                        <div class="value-line">
                                            <div class="value-title">Сума до сплати</div>
                                            <div class="align-right">
                                                <div class="value-border"></div>
                                                <div class="value"><?= $house['debt_sum_str']; ?></div>
                                            </div>
                                        </div>
                                        <div class="value-line small-line">
                                            <div class="value-title">Сплачено за <?= $house['on_this_month']; ?></div>
                                            <div class="align-right">
                                                <div class="value-border"></div>
                                                <div class="value"><?= $house['oplat_this_month_str']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            ?>
                            <div class="align-center">
                                <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $house['id']; ?>/" class="btn green bold">Перейти до об'єкту</a>
                            </div>
                        </div>
                        <?php
                    }
                ?>
            </div>
            <?php
        }
    ?>

    <div class="btn green bold big add-new" onclick="$('#add-object-form').slideToggle(300);"><div class="icon-objects"></div>Додати новий будинок або квартиру</div>

    <div id="add-object-form" class="add-object-form" style="display:none;">
        <form class="form-block" method="post" action="<?= BASE_URL; ?>/post/cabinet/objects/">
            <?php require_once(ROOT . '/protected/scripts/cabinet/objects-add-form.php'); ?>
        </form>
    </div>
</div>
