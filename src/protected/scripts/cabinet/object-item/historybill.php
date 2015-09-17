<?php
    try {
        $currMonth = date("n");
        $years = array();
        $debt = new KomDebt();
        
        for ($i=date("Y"); $i>=date("Y")-3; $i--) {
            $years[] = $i;
        }

        if ($currMonth == 1) {
            $previousMonth = "12"; 
            $previousYear = date("Y") - 1;
        } else {
            $previousMonth = $currMonth-1;
            $previousMonth = date("m", strtotime("01-".$previousMonth."-".date("Y")));
            $previousYear = date("Y");
        }

        $_need_month = $previousMonth;
        $_need_year = $previousYear;

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

        // // пока не обрабатываем
        // $_POST['service'];

        $dateBegin = "1.".$_need_month.".".$_need_year;
        $debtData = $debt->getHistoryBillData($object['flat_id'], $dateBegin);

        if (empty($debtData['bank'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }

        $have_error = false;

    } catch(Exception $e) {
        $have_error = true;
        $error = $e->getMessage();
    }
?>
<form class="filters-form" action="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/historybill/" method="get">
    <div class="filter">
        <div class="dotted-select-box with-icon">
            <div class="icon calendar"></div>
            <select class="dotted-select" name="month">
                <?php
                    foreach ($MONTHS_NAME as $key => $month) {
                        ?><option value="<?= strtolower($month['en']); ?>" <?= ($_need_month == $key) ? 'selected' : ''; ?>><?= $month['ua']; ?></option> <?php
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
                <th>Дата cплати</th>
                <th class="td-sum">Сума, грн</th>
                <th class="td-last">Перiод сплати</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $bank_counter = 0;

                foreach ($debtData['bank'] as $key => $bank) {
                    $bank_counter++;

                    ?>
                    <tr class="bank-name">
                        <td colspan="4" class="first">
                            <b>БАНК: </b><?= $bank['NAMEOKP']; ?>, <b>КАСА: </b> <?= $bank['KASSA']; ?>
                        </td>
                    </tr>
                    <?php
                        $counter = 0;
                        
                        foreach ($bank['data'] as $item) {
                            $counter++;

                            $no_border = (($counter == count($bank['data'])) && ($bank_counter < count($debtData['bank'])));
                            $pdate = DateTime::createFromFormat('d.m.y H:i:s', $item['PDATE']);
                            ?>
                            <tr class="item-row <?= ($no_border) ? 'no-border' : ''; ?> <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                                <td class="first">
                                    <span class="name-plat"><?= $item['NAME_PLAT']; ?></span> <br>
                                    <span class="name-firme"><?= $item['NAME_FIRME']; ?></span>
                                    <span class="abcount">р/с: <?= $item['ABCOUNT']; ?></span> <br>
                                    <div class="address"><?= $object['address']; ?></div>
                                </td>
                                <td>
                                    <span class="date-day"><?= $pdate->format('d/m/y'); ?></span><br>
                                    <span class="date-time"><?= $pdate->format('H:i:s'); ?></span>
                                </td>
                                <td class="align-right">
                                    <?php
                                        $summ = explode(',', $item['SUMM']);
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                </td>
                                <td class="align-center">
                                    <?php
                                        if (!$item['DBEGIN'] || !$item['DEND']) {
                                            echo '—';
                                        } else {
                                            ?>
                                            з <?= $item['DBEGIN']; ?><br>
                                            по <?= $item['DEND']; ?>
                                            <?php
                                        }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                }
            ?>
        </tbody>
    </table>
</div>