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

        if (empty($debtData['data'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }

        $prev_month = DateTime::createFromFormat('d.m.Y', $debtData['previous_date']);
        $prev_month_when = $MONTHS_WHEN[$prev_month->format('n')]['ua'];

        $debtData['date'] = str_replace(' ', '&nbsp;', '01 ' . $MONTHS[(int)$new_month] . ' ' . $new_year);
        $debtData['previous_month'] = $MONTHS_NAME[$prev_month->format('n')]['ua']['small']; // это просто приводим в нижний регистр
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
                <th rowspan="2" class="first">Послуга /<br> одержувач коштів</th>
                <th rowspan="2" class="td-sum">Заборг. / переплата на <?= date('d.m', date_timestamp_get($prev_month)); ?>, грн</th>
                <th rowspan="2">Тариф, грн</th>
                <th rowspan="2">Нараховано за <?= $debtData['previous_month']; ?>, грн *</th>
                <th colspan="2" style="text-align:center; border-bottom: solid 1px #fff;">Субсидія,&nbsp;грн</th>
                <th rowspan="2">До сплати за <?= $debtData['previous_month']; ?>, грн</th>
                <th rowspan="2" class="td-sum">Сплачено у <?= $prev_month_when; ?>, грн **</th>
            </tr>
            <tr>
                <th style="font-size: 12px; padding-left: 0;">Розмір</th>
                <th style="font-size: 12px; padding-right: 0; cursor: help;" title="Обов’язковий платіж">Об.&nbsp;платіж</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $firm_counter = 0;

                foreach ($debtData['data'] as $key => $firm) {
                    $firm_counter++;
                    ?>
                    <tr class="bank-name">
                        <td class="first" colspan="8">
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
                                <td><?= $item['SUBS']; ?></td>
                                <td><?= $item['SUMM_OBL_PAY']; ?></td>
                                <td>
                                    <?php
                                        $summ = floatval(str_replace(",", ".", $item['SUMM_DOLG']));
                                        $class = ($summ < 0) ? 'overpay' : (($summ > 0) ? 'dept' : '');
                                        $summ = explode(',', $item['SUMM_DOLG']);
                                    ?>
                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                </td>
                                <td><?= $item['OPLAT']; ?></td>
                            </tr>
                            <?php
                        }
                }
            ?>
            <tr class="item-row hints-tr">
                <td class="first" colspan="8">
                    <b class="hint-star">*</b> — з врахуванням пільг та перерахунків <br>
                    <b class="hint-star">**</b> — довідково <br>
                </td>
            </tr>
        </tbody>
    </table>
</div>
