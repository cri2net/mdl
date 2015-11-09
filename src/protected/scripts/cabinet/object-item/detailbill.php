<?php
    try {
        $currMonth = date("n");
        $years = [];
        $debt = new KomDebt();
        
        for ($i=date("Y"); $i>=2012; $i--) {
            $years[] = $i;
        }

        $_need_firm = !empty($_GET['service']) ? intval($_GET['service']) : 0;
        $filter = ($_need_firm > 0);

        if (isset($_GET['month'])) {
            foreach ($MONTHS_NAME as $key => $value) {
                if (strtolower($value['en']) == strtolower($_GET['month'])) {
                    $_need_month = $key;
                }
            }
        }

        if (isset($_GET['year']) && in_array((int)$_GET['year'], $years)) {
            $_need_year = (int)$_GET['year'];
        }


        // Если мы смотрим начисления с 1.10 по 1.11, то отдадутся за сентябрь, а не за октябрь!!!
        // И это типа правильно.
        // Короче, если пользователь выставил фильтр по дате,
        // то DBEGIN в запросе к ораклу формируем на месяц позже.

        if (isset($_need_month) && isset($_need_year)) {
            $dateBegin = "1.".$_need_month.".".$_need_year;
            $time = DateTime::createFromFormat('j.m.Y', $dateBegin);
            $time = strtotime('first day of next month', date_timestamp_get($time));
            $dateBegin = date('j.m.Y', $time);
            $firmData = $debt->getUniqueFirmName($object['flat_id'], $dateBegin, 100);
        } else {
            $firmData = $debt->getUniqueFirmName($object['flat_id'], null, 0, $real_timestamp);
            $dateBegin = date('1.m.Y', $real_timestamp);
            $_need_month = date('m', strtotime('first day of previous month', $real_timestamp));
            $_need_year = date('Y', strtotime('first day of previous month', $real_timestamp));
        }
        
        $debtData = $debt->getUniqueFirm($object['flat_id'], $_need_firm, $dateBegin, $filter);
        $debtData['date'] = str_replace(' ', '&nbsp;', '01 ' . $MONTHS[(int)$new_month] . ' ' . $new_year);

        if (empty($debtData['data'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }
        $prev_month = DateTime::createFromFormat('d.m.Y', $debtData['previous_date']);
        $prev_month_when = $MONTHS_WHEN[$prev_month->format('n')]['ua'];
        $have_error = false;

    } catch(Exception $e) {
        $have_error = true;
        $error = $e->getMessage();
    }
?>
<form class="filters-form" action="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/detailbill/" method="get">
    <div class="filter">
        <div class="dotted-select-box with-icon">
            <div class="icon calendar"></div>
            <select class="dotted-select" name="month">
                <?php
                    foreach ($MONTHS_NAME as $key => $month) {
                        ?><option value="<?= strtolower($month['en']); ?>" <?= ($_need_month == $key) ? 'selected' : ''; ?>><?= $month['ua']['small']; ?></option> <?php
                    }
                ?>
            </select>
        </div>
        <div class="dotted-select-box">
            <select class="dotted-select" name="year">
                <?php
                    foreach ($years as $year) {
                        ?><option <?= ($_need_year == $year) ? 'selected' : ''; ?>><?= $year; ?></option> <?php
                    }
                ?>
            </select>
        </div>
        <div class="dotted-select-box with-icon">
            <div class="icon services"></div>
            <select class="dotted-select service-select" name="service">
                <option value="">пiдприємство</option>
                <?php
                    if (!empty($firmData)) {
                        foreach ($firmData as $key => $firm) {
                            ?><option value="<?= $key; ?>" <?= ($_need_firm == $key) ? 'selected' : ''; ?>><?= $firm['name']; ?></option> <?php
                        }
                    }
                ?>
            </select>
        </div>
        <button class="btn green bold">Фільтрувати</button>
    </div>
</form>
<?php
    if ($have_error) {
        ?><h2 class="big-error-message"><?= $error; ?></h2> <?php
        return;
    }
?>
<div class="real-full-width-block">
    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first">Послуга, <br> комунальне пiдприємство</th>
                <th class="td-sum">Борг на <?= $debtData['previous_date']; ?></th>
                <th>Тариф, грн</th>
                <th>Нараховано за <?= $debtData['previous_month']; ?></th>
                <th class="td-sum">Сплачено у <?= $prev_month_when; ?></th>
                <th>Субсидія,<br>компенсація</th>
                <th>Борг на <?= $debtData['dbegin']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $firm_counter = 0;

                foreach ($debtData['data'] as $key => $firm) {
                    $firm_counter++;

                    ?>
                    <tr class="bank-name">
                        <td colspan="7" class="first">
                            <span class="name-plat">
                                <?= $debtData['firm'][$key]['name']; ?>, <?= $debtData['firm'][$key]['FIO']; ?>
                            </span>
                            <span class="abcount">(р/с: <?= $debtData['firm'][$key]['ABCOUNT']; ?>)</span>
                            <?php
                                if (trim($debtData['firm'][$key]['TLF']) != "") {
                                    ?> (Телефон: <?= $debtData['firm'][$key]['TLF']; ?>) <?php
                                }
                            ?>
                            <br>
                            <?php
                                if (!empty($debtData['firm'][$key]['lgoti'])) {
                                    ?>
                                    Льготы: <?= $debtData['firm'][$key]['lgoti']['NAIM_LG']; ?>,
                                    <?= $debtData['firm'][$key]['lgoti']['PROC_LG']; ?>%
                                    (количество льготников: <?= $debtData['firm'][$key]['lgoti']['KOL_LGOT']; ?>)
                                    <br>
                                    <?php
                                }
                            ?>
                            <div class="address"><?= $object['address']; ?></div>
                        </td>
                    </tr>
                    <?php
                        $counter = 0;
                        
                        foreach ($firm as $item) {
                            $counter++;

                            $no_border = (($counter == count($firm)) && ($firm_counter < count($debtData['data'])));
                            ?>
                            <tr class="item-row <?= ($no_border) ? 'no-border' : ''; ?> <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                                <td class="first">
                                    <span class="name-firme"><?= $item['NAME_PLAT']; ?></span> <br>
                                </td>
                                <td>
                                    <?php
                                        $summ = floatval(str_replace(",", ".", $item['ISXDOLG']));
                                        $class = ($summ < 0) ? 'overpay' : (($summ > 0) ? 'dept' : '');
                                        $summ = explode(',', $item['ISXDOLG']);
                                    ?>
                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                </td>
                                <td><?= $item['TARIF']; ?></td>
                                <td>
                                    <?php
                                        $summ = floatval(str_replace(",", ".", $item['SUMM_MONTH']));
                                        $class = ($summ > 0) ? 'dept' : '';
                                        $summ = explode(',', $item['SUMM_MONTH']);
                                    ?>
                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                </td>
                                <td><?= $item['OPLAT']; ?></td>
                                <td><?= $item['SUBS']; ?></td>
                                <td>
                                    <?php
                                        $summ = floatval(str_replace(",", ".", $item['SUMM_DOLG']));
                                        $class = ($summ < 0) ? 'overpay' : (($summ > 0) ? 'dept' : '');
                                        $summ = explode(',', $item['SUMM_DOLG']);
                                    ?>
                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                </td>
                            </tr>
                            <?php
                        }
                }
            ?>
        </tbody>
    </table>
    <?php
        if ($debtData['counter']) {
            ?>
            <table class="full-width-table datailbill-table no-border">
                <thead>
                    <tr>
                        <th class="counters-th" colspan="8">ПОКАЗАННЯ ЛІЧИЛЬНИКІВ</th>
                    </tr>
                    <tr>
                        <th colspan="3" class="first">Послуга</th>
                        <th>№ ліч.</th>
                        <th>Поп. пок., м&sup3;/КвтЧ</th>
                        <th>Пот. пок., м&sup3;/КвтЧ</th>
                        <th>Різниця, м&sup3;/КвтЧ</th>
                        <th>Тариф, грн</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $firm_counter = 0;
                        
                        foreach ($debtData['firm'] as $key => $firm) {
                            
                            if (!empty($firm['counter'])) {
                                $firm_counter++;

                                ?>
                                <tr class="bank-name">
                                    <td colspan="8" class="first">
                                        <span><?= $firm['name']; ?>, <?= $debtData['firm'][$key]['FIO']; ?>. (р/с: <?= $debtData['firm'][$key]['ABCOUNT']; ?>)</span>
                                    </td>
                                </tr>
                                <?php
                                    $counter = 0;

                                    foreach ($firm['counter'] as $counter) {
                                        $counter++;

                                        $no_border = (($counter == count($firm['counter'])) && ($firm_counter < count($debtData['firm'])));
                                        ?>
                                        <tr class="item-row <?= ($no_border) ? 'no-border' : ''; ?> <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                                            <td class="first" colspan="3"><?= $counter['NAME_PLAT']; ?></td>
                                            <td><?= $counter['COUNTER_NO']; ?></td>
                                            <td><?= $counter['OLD_VALUE']; ?></td>
                                            <td>&mdash;</td>
                                            <td>&mdash;</td>
                                            <td class="last"><?= $counter['TARIF']; ?></td>
                                        </tr>
                                        <?php
                                    }
                            }

                        }
                    ?>
                </tbody>
            </table>
            <?php
        }
    ?>
</div>
