<body>
<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container">
    <content>
        <?php
            if (isset($_SESSION['objects-auth']['status']) && !$_SESSION['objects-auth']['status']) {
                ?>
                <h3 class="error"><?= $_SESSION['objects-auth']['error']['text']; ?></h3>
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

                        $flats[$i]['payed'] = (($tmp_oplat > 0) && ($tmp_oplat >= $tmp_debt_summ)) ? 'bbox-green' : '';
                        $flats[$i]['oplat_this_month'] = $oplat;
                        $flats[$i]['oplat_this_month_str'] = substr($oplat, 0, strlen($oplat) - 3);
                        $flats[$i]['oplat_this_month_str'] .= '<span class="small">'.substr($oplat, strlen($oplat) - 3).' <span class="currency">&#8372;</span></span>';

                        $flats[$i]['debt_sum_str'] = substr($flats[$i]['debt_sum'], 0, strlen($flats[$i]['debt_sum']) - 3);
                        $flats[$i]['debt_sum_str'] .= '<span class="small">'.substr($flats[$i]['debt_sum'], strlen($flats[$i]['debt_sum']) - 3).' <span class="currency">&#8372;</span></span>';
                    } catch (Exception $e) {
                        $flats[$i]['error'] = 1;
                    }
                }
            } catch (Exception $e) {
                ?><h3 class="error"><?= $e->getMessage(); ?></h3> <?php
                return;
            }
        ?>
        <div class="cabinet-objects">
            <?php
                if (count($flats) > 0) {
                    ?>
                    <div class="houses_line row">
                        <?php
                            for ($i=0; $i < count($flats); $i++) {
                                $flat = $flats[$i];

                                if (($i % 2 == 0) && ($i > 0)) {
                                    ?></div><div style="margin-top: 30px;" class="houses_line row"><?php
                                }
                                ?>
                                <div class="col-md-6">
                                    <div class="house_item flat matchHeight <?= $flat['payed']; ?>">
                                        <div class="payed-icon"></div>
                                        <div class="title">
                                            <?php
                                                if ($flat['title']) {
                                                    ?>
                                                    <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $flat['id']; ?>/" class="header <?php /* edit */ ?>"><?= htmlspecialchars($flat['title']); ?></a>
                                                    <div class="address"><?= $flat['address']; ?></div>
                                                    <?php
                                                } else {
                                                    $street_name = ($flat['street_name'] !== $flat['street_name_full'])
                                                        ? $street_name = '<span title="'. $flat['street_name_full'] .'">' . $flat['street_name'] . '</span>'
                                                        : $flat['street_name'];
                                                    ?>
                                                    <a href="<?= BASE_URL; ?>/cabinet/objects/<?= $flat['id']; ?>/" class="address">
                                                        <?= $flat['detail_address']['city']; ?>, <br> <?= $street_name; ?> <br> <?= $flat['detail_address']['house']; ?> кв. <?= $flat['detail_address']['flat']; ?>
                                                    </a>
                                                    <?php
                                                }
                                            ?>
                                        </div>
                                        <a onclick="$('#remove_object_id').val('<?= $flat['id']; ?>').parent().submit();" class="remove">&times;</a>
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
                                                    <div class="bydate">Рахунок за <?= $MONTHS_NAME[date('n', $flat['timestamp'])]['ua']['small']; ?> <?= date('Y', $flat['timestamp']); ?></div>
                                                    <div class="value-line row">
                                                        <div class="value-title value-title-blue col-lg-9 col-md-7 col-ms-6">Сума до сплати</div>
                                                        <div class="align-right col-lg-3 col-md-5 col-ms-6 pull-right">
                                                            <div class="value">
                                                                <?= $flat['debt_sum_str']; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="value-line row">
                                                        <div class="value-title value-title-green col-lg-9 col-md-9 col-ms-6">Сплачено за <?= $flat['on_this_month']; ?></div>
                                                        <div class="align-right col-lg-3 col-md-3 col-ms-6 pull-right">
                                                            <div class="value">
                                                                <?= $flat['oplat_this_month_str']; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        ?>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                    <?php
                }
            ?>
        </div>

        <?php
            if ((Authorization::isLogin() && (Flat::getFlatCount() < Flat::getMaxUserFlats()))) {
                ?>
                <a class="btn btn-gray btn-lg add-new" onclick="$('#add-object-form').slideToggle(300);"><span>Додати об'ект</span></a>

                <div id="add-object-form" class="add-object-form" style="display:none;">
                    <form class="form-block" method="post" action="<?= BASE_URL; ?>/post/cabinet/objects/">
                        <?php require_once(PROTECTED_DIR . '/scripts/cabinet/objects-add-form.php'); ?>
                    </form>
                </div>
                <?php
            }
        ?>
    </content>
</div>

<form id="remove_object_form" style="display: none;" action="<?= BASE_URL; ?>/post/cabinet/object-item/edit/" method="post">
    <input type="hidden" name="flat_id" id="remove_object_id">
    <input name="delete_object" value="1" type="hidden">
</form>
