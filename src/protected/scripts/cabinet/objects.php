<div class="h1-line-cabinet">
    <h1 class="big-title">Об’єкти</h1>
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
        $flats = Flat::getUserFlats($user_id);
        $debt = new KomDebt();
        
        for ($i=0; $i < count($flats); $i++) {

            try {
                $debtData = $debt->getData($flats[$i]['flat_id'], null, 0);
                $dateBegin = date('1.m.Y', $debtData['timestamp']);
                $flats[$i]['timestamp'] = $debtData['timestamp'];
                
                // Оплаты за месяц надо запрашивать, передавая dbegin на 1 число след. след. месяца от dbegin оплат
                // Чтобы было: начисления за ноябрь, dbegin = 1.10, а оплаты dbegin оплат = 1.12
                $oplat_timestamp = strtotime('first day of next month', $debtData['timestamp']);
                $oplat_timestamp = strtotime('first day of next month', $oplat_timestamp);

                $flats[$i]['debt_sum'] = $debtData['full_dept'];
                
                $flats[$i]['on_this_month'] = $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small'];
                $flats[$i]['date'] = $debtData['date'];

                try {
                    $oplat = $debt->getPayOnThisMonth($flats[$i]['flat_id'], date('1.m.Y', $oplat_timestamp));
                } catch (Exception $e) {
                    $oplat = '0,00';
                }

                $tmp_oplat = (double)str_replace(',', '.', $oplat);
                $tmp_debt_summ = (double)str_replace(',', '.', $flats[$i]['debt_sum']);

                $flats[$i]['payed'] = (($tmp_oplat > 0) && ($tmp_oplat >= $tmp_debt_summ))?'bbox-green':'';
                $flats[$i]['oplat_this_month'] = $oplat;
                $flats[$i]['oplat_this_month_str'] = substr($oplat, 0, strlen($oplat) - 3);
                $flats[$i]['oplat_this_month_str'] .= '<span class="small">'.substr($oplat, strlen($oplat) - 3).' <span class="currency">грн</span></span>';

                $flats[$i]['debt_sum_str'] = '<span class="right">'.substr($flats[$i]['debt_sum'], 0, strlen($flats[$i]['debt_sum']) - 3);
                $flats[$i]['debt_sum_str'] .= '<span class="small">'.substr($flats[$i]['debt_sum'], strlen($flats[$i]['debt_sum']) - 3).' <span class="currency">грн</span></span>';
                $flats[$i]['debt_sum_str'] .= '</span>';
            } catch (Exception $e) {
                $flats[$i]['error'] = 1;
            }
        }
    } catch (Exception $e) {
        ?><h2 class="big-error-message"><?= $e->getMessage(); ?></h2> <?php
        return;
    }
?>
<div class="cabinet-objects">
    <?php
        if (count($flats) > 0) {
            ?>
            <div class="houses_line">
                <?php
                    for ($i=0; $i < count($flats); $i++) {
                        $flat = $flats[$i];

                        if (($i % 2 == 0) && ($i > 0)) {
                            ?></div><div class="houses_line"><?php
                        }
                        ?>
                        <div class="house_item <?= $flat['payed']; ?>">
                            <div class="payed-icon"></div>
                            <div class="title">
                                <?php
                                    if ($flat['title']) {
                                        echo htmlspecialchars($flat['title']);
                                        ?>
                                        <div class="address"><?= $flat['address']; ?></div>
                                        <?php
                                    } else {
                                        $street_name = ($flat['street_name'] !== $flat['street_name_full'])
                                            ? $street_name = '<span title="'. $flat['street_name_full'] .'">' . $flat['street_name'] . '</span>'
                                            : $flat['street_name'];
                                        ?>
                                        <?= $flat['detail_address']['city']; ?>, <br> <?= $street_name; ?> <br> <?= $flat['detail_address']['house']; ?> кв. <?= $flat['detail_address']['flat']; ?>
                                        <?php
                                    }

                                    if (!$flat['error']) {
                                        ?>
                                        <div class="bydate">рахунок за <?= $MONTHS_NAME[date('n', $flat['timestamp'])]['ua']['small']; ?> <?= date('Y', $flat['timestamp']); ?> року</div>
                                        <?php
                                    }
                                ?>
                            </div>
                            <?php
                                if ($flat['error']) {
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
                                                <div class="value"><?= $flat['debt_sum_str']; ?></div>
                                            </div>
                                        </div>
                                        <div class="value-line small-line">
                                            <div class="value-title">Сплачено за <?= $flat['on_this_month']; ?></div>
                                            <div class="align-right">
                                                <div class="value-border"></div>
                                                <div class="value"><?= $flat['oplat_this_month_str']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            ?>
                            <div class="align-center">
                                <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $flat['id']; ?>/" class="btn green bold">Перейти до об’єкту</a>
                            </div>
                        </div>
                        <?php
                    }
                ?>
            </div>
            <?php
        }
    ?>
    <br>
    <?php
        $verifiedEmail = (int)($_SESSION['auth']['verified_email']) == 1;
        if($verifiedEmail) {
        ?>
        <div class="btn green bold big add-new" onclick="$('#add-object-form').slideToggle(300);"><div class="icon-objects"></div>Додати новий будинок або квартиру</div>
        <div id="add-object-form" class="add-object-form" style="display:none;">
            <form class="form-block" method="post" action="<?= BASE_URL; ?>/post/cabinet/objects/">
                <?php require_once(PROTECTED_DIR . '/scripts/cabinet/objects-add-form.php'); ?>
            </form>
        </div>
        <?php
        } else {
        ?>
        <div style="border:solid 1px #f00; text-align:center; padding:2em;" >
        Щоб додати об'єкт до системи, <a href="<?= BASE_URL ?>/cabinet/verify-email/" >підтвердіть</a> адресу Вашої електроної пошти
        </div>
        <?php
        }
    ?>
    
</div>
