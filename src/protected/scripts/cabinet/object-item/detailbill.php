<?php
    try {
        $currMonth = date("n");
        $years = [];
        
        for ($i=date("Y"); $i>=2017; $i--) {
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

        if (empty($debtData['data'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }

        $prev_month = DateTime::createFromFormat('d.m.Y', $debtData['previous_date']);
        $prev_month_when = $MONTHS_WHEN[$prev_month->format('n')]['ua'];

        $debtData['previous_month'] = $MONTHS_NAME[$prev_month->format('n')]['ua']['small']; // это просто приводим в нижний регистр
        $have_error = false;

    } catch (Exception $e) {
        $have_error = true;
        $error = $e->getMessage();
    }
?>

<div class="cabinet-settings object-item object-item-bill">
    <form class="real-full-width-block" id="object-item-detailbill-form" action="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/detailbill/">
        <div class="row table-caption">
            <div class="calendar col-lg-12 form__input-container">
                <select name="month" class="form__input--history form__input form__input--history--outer">
                    <?php
                        foreach ($MONTHS_NAME as $key => $month) {
                            ?><option value="<?= strtolower($month['en']); ?>" <?= ($_need_month == $key) ? 'selected' : ''; ?>><?= $month['ua']['small']; ?></option> <?php
                        }
                    ?>
                </select>
                <select class="dotted-select form__input--history form__input form__input--history--outer" name="year">
                    <?php
                        foreach ($years as $year) {
                            ?><option <?= ($_need_year == $year) ? 'selected' : ''; ?>><?= $year; ?></option> <?php
                        }
                    ?>
                </select>

                <div class="dotted-select-box with-icon form__input--history form__input--history--outer">
                    <div class="icon services form__input--history"></div>
                    <select class="dotted-select service-select form__input--history form__input form__input--inner-history" name="service">
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
                                
                <a onclick="$('#object-item-detailbill-form').submit();" class="btn btn-xs button button__form button__form--register"><span class="fa  fa-calendar"></span> Показати</a>
            </div>
        </div>
    </form>
    <?php
        if ($have_error) {
            ?><h2 class="big-error-message"><?= $error; ?></h2> <?php
        } else {
            ?>
            <div class="table-responsive border-top">
                <div class="full-width-table datailbill-table no-border">
                    <?php
                        if (!$have_error) {
                            ?>
                            <div>
                                <?php

                                foreach ($debtData['data'] as $key => $firm) {
                                    ?>
                                    <?php
                                    
                                    $row = 0;
                                    foreach ($firm as $item) {

                                        $row++;
                                        ?>
                                        <div class="detail-bill detail-bill--outer">
                                            <div class="detail-bill__cell">
                                                <p class="detail-bill__text detail-bill__cell-head">Назва послуги /<br> одержувач коштів</p>
                                                <p class="detail-bill__text">
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
                                                        <?php
                                                }
                                            ?></strong>
                                                    <?= $object['address']; ?>
                                                    <strong class="green"><?= $item['NAME_PLAT']; ?></strong>
                                                </p>
                                            </div>
                                            <div class="detail-bill__cell">
                                                <p class="detail-bill__text detail-bill__cell-head">Заборгованість/<br>переплата на <?= date('d.m', date_timestamp_get($prev_month)); ?>, грн</p>
                                                <?php
                                                    $summ = floatval(str_replace(",", ".", $item['ISXDOLG']));
                                                    $class = ($summ < 0) ? 'overpay' : (($summ > 0) ? 'dept' : '');
                                                    $summ = explode(',', $item['ISXDOLG']);
                                                ?>
                                                <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                            </div>
                                            <div class="detail-bill__cell">
                                                <p class="detail-bill__text detail-bill__cell-head">Тариф,<br> грн</p>
                                                <?php
                                                    $summ = explode(',', $item['TARIF']);
                                                ?>
                                                <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                            </div>
                                            <div class="detail-bill__cell">
                                                <p class="detail-bill__text detail-bill__cell-head">Нараховано за <br><?= $debtData['previous_month']; ?>, грн*</p>
                                                <?php
                                                    $summ = floatval(str_replace(",", ".", $item['SUMM_MONTH']));
                                                    $class = ($summ > 0) ? 'dept' : '';
                                                    $summ = explode(',', $item['SUMM_MONTH']);
                                                ?>
                                                <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                            </div>
                                            <div class="detail-bill__cell"->
                                                <p class="detail-bill__text detail-bill__cell-head">Субсидія, грн  розмір |<br> об. платіж</p>
                                                <p class="detail-bill__text">
                                                    <?php
                                                        $summ = explode(',', $item['SUBS']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                    <span>&nbsp;</span>
                                                    <?php
                                                        $summ = explode(',', $item['SUMM_OBL_PAY']);
                                                    ?>
                                                    <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                                </p>
                                            </div>
                                            <div class="detail-bill__cell">
                                                <p class="detail-bill__text detail-bill__cell-head">Сплачено у <br><?= $prev_month_when; ?>, грн**</p>
                                                <?php
                                                    $summ = explode(',', $item['OPLAT']);
                                                ?>
                                                <span class="item-summ"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <?php
                        }
                    ?>
                </div>
            </div>
            <div class="hints detail-bill__hints detail-bill__hints--outer">
                <b class="hint-star">*</b> — з врахуванням пільг та перерахунків <br>
                <b class="hint-star">**</b> — довідково <br>
            </div>
            <?php
        }
    ?>
</div>
