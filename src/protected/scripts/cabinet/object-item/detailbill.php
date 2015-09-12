<?php
    $currMonth = date("n");

    if ($currMonth == 1) {
        $previousMonth = "12"; 
        $previousYear = date("Y") - 1;
    } else {
        $previousMonth = $currMonth-1;
        $previousMonth = date("m", strtotime("01-".$previousMonth."-".date("Y")));
        $previousYear = date("Y");
    }
        
    try {
        $debt = new KomDebt();
        
        if (!isset($_POST['month'])) {
            $_POST['month'] = date('m');
            $_POST['year'] = date('Y');
        }

        // это месяц из фильтра, надо обработать его
        // $month = !empty($_POST['month'])?$_POST['month'] : $previousMonth;
        $_need_month = $previousMonth;

        $year = !empty($_POST['year'])?$_POST['year']:$previousYear;
        $dateBegin = "1.".$_need_month.".".$year;
        $debtData = $debt->getHistoryBillData($object['plat_code'], $dateBegin);
        $generalData = $debt->getGenerealData($object['plat_code']);
        $billOnDate = $generalData['date'];
        $have_error = false;

    } catch(Exception $e) {
        $have_error = true;
        $error = $e->getMessage();
    }
    
    $years = array();
    for ($i=date("Y"); $i>=date("Y")-3; $i--) {
        $years[] = $i;
    }
    
    if (!empty($_SESSION['debt_date'])){
        $billOnDate = $_SESSION['debt_date'];
    } else {
        $_SESSION['debt_date'] = $billOnDate;
    }
?>
<form class="filters-form" action="<?= BASE_URL; ?>/post/cabinet/object-item/detailbill/" method="post">
    <div class="filter">
        <div class="dotted-select-box with-icon">
            <div class="icon calendar"></div>
            <select class="dotted-select" name="month">
                <?php
                    foreach ($MONTHS_NAME as $key => $month) {
                        ?><option value="<?= $key; ?>" <?= ($_need_month == $key) ? 'selected' : ''; ?>><?= $month['ua']; ?></option> <?php
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
            <select class="dotted-select" name="month">
                <option value="">послуга</option>
            </select>
        </div>
        <button class="btn green bold">Фільтрувати</button>
        <input name="flat_id" type="hidden" value="<?= $object['id']; ?>" />
    </div>
</form>
<?php
    if ($have_error) {
        ?><h2 class="big-error-message"><?= $error; ?></h2> <?php
        return;
    }

    if (count($debtData['bank']) == 0) {
        return;
    }
?>
<table>
    <thead>
        <tr>
            <th width="44%">Послуга, <br> комунальне пiдприємство</th>
            <th width="8%">Дата cплати</th>
            <th class="td-sum" width="8%">Сумма, грн</th>
            <th class="td-last" width="8%">Перiод оплати</th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach ($debtData['bank'] as $key => $bank) {
                ?>
                <tr class="gray">
                    <td colspan="4">
                        <b>БАНК: </b><?= $bank['NAMEOKP']; ?>, <b>КАССА: </b> <?= $bank['KASSA']; ?>
                    </td>
                </tr>
                <?php
                foreach ($bank['data'] as $item) {
                    ?>
                    <tr>
                        <td>
                            <span><?= $item['NAME_PLAT']; ?></span><br>
                            <?= $item['NAME_FIRME']; ?>
                        </td>
                        <td>
                            <?= $item['PDATE']; ?><br>
                            <b>р/с: </b><?= $item['ABCOUNT']; ?>
                        </td>
                        <td class="td-sum">
                            <b><?= $item['SUMM']; ?></b>
                        </td>
                        <td class="td-last">
                            з <?= $item['DBEGIN']; ?><br>
                            по <?= $item['DEND']; ?>
                        </td>
                    </tr>
                    <?php
                }
            }
        ?>
    </tbody>
</table>
