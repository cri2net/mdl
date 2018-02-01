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

        <div class="cabinet-settings object-item object-item-bill">
            <form class="real-full-width-block" id="object-item-detailbill-form" action="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/detailbill/" method="get">
                <div class="row table-caption">
                    <div class="calendar col-lg-12">
                        <div class="dropdown">
                            <button class="select-green dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-month" value="1">
                                <?= $MONTHS_NAME[(int)$_need_month]['ua']['small']; ?>
                                <span class="caret"></span>
                            </button>
                            <input type="hidden" id="detailbill-filter-month" value="<?= strtolower($MONTHS_NAME[(int)$_need_month]['en']); ?>" name="month" />
                            <!-- <?= ($_need_month == $key) ? 'selected' : ''; ?> -->
                            <ul class="dropdown-menu" aria-labelledby="select-month">
                                <?php
                                    foreach ($MONTHS_NAME as $key => $month) {
                                        ?>
                                        <li><a onclick="$('#detailbill-filter-month').val('<?= strtolower($month['en']); ?>');" id="detailbill-filter-month-a-<?= strtolower($month['en']); ?>" data-value="<?= strtolower($month['en']); ?>"><?= $month['ua']['small']; ?></a></li>
                                        <?php
                                    }
                                ?>
                            </ul>
                            <script>
                                $(document).ready(function(){
                                    $('#detailbill-filter-month-a-<?= $_need_month; ?>').click();
                                });
                            </script>
                        </div>
                        <div class="dropdown">
                            <button class="select-green dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" id="select-year" value="<?= $_need_year; ?>">
                                <?= $_need_year; ?>
                                <span class="caret"></span>
                            </button>
                            <input type="hidden" id="detailbill-filter-year" value="<?= $_need_year; ?>" name="year" />
                            <ul class="dropdown-menu" aria-labelledby="select-month">
                                <?php
                                    foreach ($years as $year) {
                                        ?>
                                        <!-- <?= ($_need_year == $year) ? 'selected' : ''; ?> -->
                                        <li><a onclick="$('#detailbill-filter-year').val('<?= $year; ?>');" id="detailbill-filter-year-a-<?= $year; ?>" data-value="<?= $year; ?>"><?= $year; ?></a></li>
                                        <?php
                                    }
                                ?>
                            </ul>
                            <script>
                                $(document).ready(function(){
                                    $('#detailbill-filter-year-a-<?= $_need_year; ?>').click();
                                });
                            </script>
                        </div>
                        <div class="dropdown">
                            <input type="hidden" id="detailbill-filter-service" value="<?= $_need_firm; ?>" name="service" />
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
                                <li><a id="detailbill-filter-sevice-a-0" onclick="$('#detailbill-filter-service').val('0');" data-value="0">Оберіть компанію</a></li>
                                <?php
                                    if (!empty($firmData)) {
                                        foreach ($firmData as $key => $firm) {
                                            ?>
                                            <li><a id="detailbill-filter-sevice-a-<?= $key; ?>" data-value="<?= $key; ?>" onclick="$('#detailbill-filter-service').val('<?= $key; ?>');" <?= ($_need_firm == $key) ? 'selected' : ''; ?>><?= $firm['name']; ?></a></li>
                                            <?php
                                        }
                                    }
                                ?>
                            </ul>
                            <script>
                                $(document).ready(function(){
                                    $('#detailbill-filter-sevice-a-<?= $_need_firm; ?>').click();
                                });
                            </script>
                        </div>                        
                        <a onclick="$('#object-item-detailbill-form').submit();" class="btn btn-xs"><span class="fa  fa-calendar"></span> Показати</a>
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="table-responsive border-top">
                    <table class="full-width-table datailbill-table no-border" id="data-table">
                        <thead>
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
                                                <strong><?= $debtData['firm'][$key]['name']; ?>,
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
                                                ?></strong>
                                                <?= $object['address']; ?>
                                            </th>
                                        </tr>
                                        <?php
                                        
                                        $row = 0;
                                        foreach ($firm as $item) {

                                            $row++;
                                            if ($row == sizeof($firm)) $tr_class = ' item-last'; else $tr_class = '';
                                            ?>
                                            <tr class="item-row <?php echo $tr_class; ?>">
                                                <td class="border-bottom header">
                                                    <strong class="green"><?= $item['NAME_PLAT']; ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                        $summ = floatval(str_replace(",", ".", $item['ISXDOLG']));
                                                        $class = ($summ < 0) ? 'overpay' : (($summ > 0) ? 'dept' : '');
                                                        $summ = explode(',', $item['ISXDOLG']);
                                                    ?>
                                                    <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>
                                                <td>
                                                    <?php
                                                        $summ = explode(',', $item['TARIF']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </td>
                                                <td>
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
                                                <td>
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
                                                <td>
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
                                <?php
                            }
                        ?>
                    </table>
                </div>
                <div class="hints">
                            <b class="hint-star">*</b> — з врахуванням пільг та перерахунків <br>
                            <b class="hint-star">**</b> — довідково <br>
                </div>
                
                    <?php

                        if (!empty($recalcData)) {
                            ?>
                            <div class="table-responsive" style="margin-top: 40px;">
                                <table class="full-width-table datailbill-table no-border">
                                    <thead>
                                        <tr>
                                            <th colspan="4" class="first">Деталі перерахунку</th>
                                        </tr>
                                        <tr class="head-gray-2">
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
                            </div>
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
