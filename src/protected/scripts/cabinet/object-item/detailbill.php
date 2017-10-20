<?php
    try {
        $currMonth = date("n");
        $years = [];
        
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

        $plat_code = KomDebt::getFlatIdOrPlatcode($object['flat_id'], $object['plat_code'], $object['city_id']);


        // Если мы смотрим начисления с 1.10 по 1.11, то отдадутся за сентябрь, а не за октябрь!!!
        // И это типа правильно.
        // Короче, если пользователь выставил фильтр по дате,
        // то DBEGIN в запросе к ораклу формируем на месяц позже.

        if (isset($_need_month) && isset($_need_year)) {
            $dateBegin = "1.".$_need_month.".".$_need_year;
            $time = DateTime::createFromFormat('j.m.Y', $dateBegin);
            $time = strtotime('first day of next month', date_timestamp_get($time));
            $dateBegin = date('j.m.Y', $time);

            $firmData = $debt->getUniqueFirmName($plat_code, $dateBegin, 100);
        } else {
            $firmData = $debt->getUniqueFirmName($plat_code, null, 0, $real_timestamp);
            $dateBegin = date('1.m.Y', $real_timestamp);
            $_need_month = date('m', strtotime('first day of previous month', $real_timestamp));
            $_need_year = date('Y', strtotime('first day of previous month', $real_timestamp));
        }
        
        $debtData = $debt->getUniqueFirm($plat_code, $_need_firm, $dateBegin, $filter);
        $recalcData = $debt->getReCalc($object['flat_id'], '0' . $dateBegin);

        if (empty($debtData['data'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }

        $prev_month = DateTime::createFromFormat('d.m.Y', $debtData['previous_date']);
        $prev_month_when = $MONTHS_WHEN[$prev_month->format('n')]['ua'];

        $debtData['previous_month'] = $MONTHS_NAME[$prev_month->format('n')]['ua']['small']; // это просто приводим в нижний регистр
        $have_error = false;

    } catch(Exception $e) {
        $have_error = true;
        $error = $e->getMessage();
    }

    $have_recalc = !empty($recalcData);
?>
<div class="container">
    <content>
        <div class="cabinet-settings object-item object-item-bill">
            <form class="real-full-width-block" action="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/detailbill/" method="get">
                <div class="thead-bg">
                    <div class="head-green"></div>
                    <div class="head-gray"></div>
                    <div class="head-green-2"></div>
                </div>

                <div class="table-responsive">
                    <table class="full-width-table datailbill-table no-border" id="data-table">
                        <thead>
                            <tr class="head-green">
                                <th colspan="6" class="align-left">
                                    <div class="calendar">
                                        <div class="dropdown">
                                            <button class="select-green dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-month" value="1">
                                                <?= $MONTHS_NAME[(int)$_need_month]['ua']['small']; ?>
                                                <span class="caret"></span>
                                            </button>
                                            <!-- <?= ($_need_month == $key) ? 'selected' : ''; ?> -->
                                            <ul class="dropdown-menu" aria-labelledby="select-month">
                                                <?php
                                                    foreach ($MONTHS_NAME as $key => $month) {
                                                        ?>
                                                        <li><a data-value="<?= strtolower($month['en']); ?>"><?= $month['ua']['small']; ?></a></li>
                                                        <?php
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                        <div class="dropdown">
                                            <button class="select-green dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-year" value="<?= $_need_year; ?>">
                                                <?= $_need_year; ?>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select-month">
                                                <?php
                                                    foreach ($years as $year) {
                                                        ?>
                                                        <!-- <?= ($_need_year == $year) ? 'selected' : ''; ?> -->
                                                        <li><a data-value="<?= $year; ?>"><?= $year; ?></a></li>
                                                        <?php
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>




                                    <div class="company">
                                        <div class="dropdown">
                                            <button class="select-green dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-company">
                                                <?php
                                                    $have_firme = false;

                                                    if (!empty($firmData)) {
                                                        foreach ($firmData as $key => $firm) {
                                                            
                                                            if ($_need_firm == $key) {
                                                                $have_firme = true;
                                                                echo $firm['name'];
                                                                break;
                                                            }
                                                        }
                                                    }

                                                    if (!$have_firme) {
                                                        echo 'Оберіть компанію';
                                                    }
                                                ?>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="select-company">
                                                <?php
                                                    if (!empty($firmData)) {
                                                        foreach ($firmData as $key => $firm) {
                                                            ?>
                                                            <li><a data-value="<?= $key; ?>" <?= ($_need_firm == $key) ? 'selected' : ''; ?>><?= $firm['name']; ?></a></li>
                                                            <?php
                                                        }
                                                    }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr class="head-gray">
                                <th class="th-header">Назва послуги /<br>одержувач коштів</th>
                                <th>Заборгованість/<br>переплата<br>на <?= date('d.m', date_timestamp_get($prev_month)); ?>, грн</th>
                                <th>Тариф,<br> грн</th>
                                <th>Нараховано за<br> <?= $debtData['previous_month']; ?>, грн*</th>
                                <?php
                                    if ($have_recalc) {
                                        ?>
                                        <th rowspan="2">Перерахунок</th>
                                        <?php
                                    }
                                ?>
                                <th>Субсидія, грн<br>  розмір | об. платіж</th>
                                <th>Сплачено у<br> <?= $prev_month_when; ?>, грн**</th>
                            </tr>                         
                        </thead>
                        <?php
                            if (!$have_error) {
                                ?>
                                <tbody>
                                    <?php

                                    foreach ($debtData['data'] as $key => $firm) {
                                        ?>
                                        <tr class="head-green-2">
                                            <th colspan="<?= ($have_recalc) ? 7 : 6; ?>">
                                                <?= $debtData['firm'][$key]['name']; ?>,
                                                <?= $debtData['firm'][$key]['FIO']; ?>
                                                (о.р. <?= $debtData['firm'][$key]['ABCOUNT']; ?>)
                                                <?php

                                                    if (trim($debtData['firm'][$key]['TLF']) != "") {
                                                        ?> (Телефон: <?= $debtData['firm'][$key]['TLF']; ?>) <?php
                                                    }

                                                    if (!empty($debtData['firm'][$key]['lgoti'])) {
                                                        ?>
                                                        Пільги: <?= $debtData['firm'][$key]['lgoti']['NAIM_LG']; ?>,
                                                        <?= $debtData['firm'][$key]['lgoti']['PROC_LG']; ?>%
                                                        (кількість пільговиків: <?= $debtData['firm'][$key]['lgoti']['KOL_LGOT']; ?>)
                                                        <br>
                                                        <?php
                                                    }
                                                ?>
                                                <?= $object['address']; ?>
                                            </th>
                                        </tr>
                                        <?php
                                        
                                        foreach ($firm as $item) {
                                            ?>
                                            <tr class="item-row">
                                                <td class="border-bottom header header-big">
                                                    <?= $item['NAME_PLAT']; ?>
                                                </td>
                                                <td class="border-bottom">
                                                    <?php
                                                        $summ = floatval(str_replace(",", ".", $item['ISXDOLG']));
                                                        $class = ($summ < 0) ? 'overpay' : (($summ > 0) ? 'dept' : '');
                                                        $summ = explode(',', $item['ISXDOLG']);
                                                    ?>
                                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>
                                                <td class="border-bottom">
                                                    <?php
                                                        $summ = explode(',', $item['TARIF']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>
                                                <td class="border-bottom">
                                                    <?php
                                                        $summ = floatval(str_replace(",", ".", $item['SUMM_MONTH']));
                                                        $class = ($summ > 0) ? 'dept' : '';
                                                        $summ = explode(',', $item['SUMM_MONTH']);
                                                    ?>
                                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>
                                                <?php
                                                    if ($have_recalc) {
                                                        ?>
                                                        <td class="border-bottom">
                                                            <?php
                                                                if (strstr($item['NAME_PLAT'], 'УТРИМАННЯ')) {
                                                                    
                                                                    $summ = 0;
                                                                    foreach ($recalcData as $tmp_item) {
                                                                        foreach ($tmp_item['list'] as $list_item) {
                                                                            $summ += $list_item['sum'];
                                                                        }
                                                                    }

                                                                    // в xml есть поле "Всього по о/рахунку(по періоду)", где дублирутеся общая сумма.
                                                                    // То есть надо на два поделить то, что выше в цикле просуммировано
                                                                    $summ /= 2;

                                                                    $summ = explode('.', $summ);
                                                                    ?>
                                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                                    <?php
                                                                } else {
                                                                    echo '—';
                                                                }
                                                            ?>
                                                        </td>
                                                        <?php
                                                    }
                                                ?>
                                                <td class="border-bottom">
                                                    <?php
                                                        $summ = explode(',', $item['SUBS']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                    <span>&nbsp;</span>
                                                    <?php
                                                        $summ = explode(',', $item['SUMM_OBL_PAY']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>                             
                                                </td>
                                                <td class="border-bottom">
                                                    <?php
                                                        $summ = explode(',', $item['OPLAT']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>                                
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="item-row hints-tr">
                                        <td class="first" colspan="6">
                                            <b class="hint-star">*</b> — з врахуванням пільг та перерахунків <br>
                                            <b class="hint-star">**</b> — довідково <br>
                                        </td>
                                    </tr>
                                </tfoot>
                                <?php
                            }
                        ?>
                    </table>
                    <?php

                        if (!empty($recalcData)) {
                            ?>
                            <table class="full-width-table datailbill-table no-border">
                                <thead>
                                    <tr>
                                        <th colspan="4" class="first">Деталі перерахунку</th>
                                    </tr>
                                    <tr>
                                        <th class="first">Назва послуги</th>
                                        <th>Початок періоду</th>
                                        <th>Кінець періоду</th>
                                        <th>Сума</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $counter = 0;
                                        $recalc_total_summ = 0;
                                        
                                        foreach ($recalcData as $item) {

                                            for ($i=0; $i < count($item['list']); $i++) {

                                                $counter++;
                                                $recalc_total_summ += $item['list'][$i]['sum'];

                                                $no_border = (($counter == count($recalcData)) && ($firm_counter < count($debtData['data'])));
                                                $summ = explode('.', $item['list'][$i]['sum']);
                                                $is_bold = (count($item['list']) - 1 == $i);
                                                ?>
                                                <tr style="<?= ($is_bold) ? 'border-bottom-width: 3px;' : ''; ?>" class="item-row <?= ($no_border) ? 'no-border' : ''; ?> <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                                                    <td class="first" style="<?= ($is_bold) ? 'font-weight: bold;' : ''; ?>"><?= $item['list'][$i]['NAME']; ?></td>
                                                    <td><?= $item['DBEGIN']; ?></td>
                                                    <td><?= $item['DEND']; ?></td>
                                                    <td>
                                                        <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        }

                                        if (count($recalcData) > 1) {
                                            
                                            $recalc_total_summ /= 2;
                                            $summ = explode('.', $recalc_total_summ);
                                            ?>
                                            <tr style="border-bottom-width: 3px;" class="item-row odd">
                                                <td colspan="3" class="first" style="font-weight: bold;">Всього за всі періоди</td>
                                                <td>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                        }
                    ?>
                </div>
            </form>
        </div>
        <?php
            if ($have_error) {
                ?><h2 class="big-error-message"><?= $error; ?></h2> <?php
            }
        ?>
    </content>
</div>
