<?php

    use cri2net\php_pdo_db\PDO_DB;

    try {
        $currMonth = date("n");
        $years = [];
        $debt = new KomDebt();
        
        for ($i=date("Y"); $i>=2012; $i--) {
            $years[] = $i;
        }

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

        if (isset($_need_month) && isset($_need_year)) {
            $dateBegin = "1.".$_need_month.".".$_need_year;
            $debtData = $debt->getHistoryBillData($plat_code, $dateBegin, 100);
        } else {
            $debtData = $debt->getHistoryBillData($plat_code, null, 0, $real_timestamp);
            $dateBegin = date('1.m.Y', $real_timestamp);
            $_need_month = date('m', $real_timestamp);
            $_need_year = date('Y', $real_timestamp);
        }

        if (empty($debtData['bank'])) {
            throw new Exception(ERROR_EMPTY_HISTORYBILL);
        }

        $have_error = false;

    } catch(Exception $e) {
        $have_error = true;
        $error = $e->getMessage();
    }
?>
        <div class="cabinet-settings object-item object-item-bill">
            <form  id="object-item-historybill-form" class="real-full-width-block" action="<?= BASE_URL; ?>/cabinet/objects/<?= $object['id']; ?>/historybill/" method="get">
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
                        <a onclick="$('#object-item-historybill-form').submit();" class="btn btn-xs"><span class="fa  fa-calendar"></span> Показати</a>             
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="table-responsive border-top">
                    <table class="full-width-table datailbill-table no-border" id="data-table">
                        <thead>
                            <tr class="head-gray">
                                <th>Номер<br>операції</th>
                                <th class="th-header">Назва послуги /<br>одержувач коштів</th>
                                <th>Дата<br> cплати</th>
                                <th>Період<br> сплати</th>
                                <th>Сума, грн</th>
                            </tr>
                        </thead>
                        <?php
                            if (!$have_error) {
                                ?>
                                <tbody>
                                    <?php
                                        $stm_find_payment = PDO_DB::prepare("SELECT id FROM " . TABLE_PREFIX . "payment WHERE reports_id_plat_klient = ? AND user_id = ? LIMIT 1");
                                        $id_p_k_to_id = [];

                                        foreach ($debtData['bank'] as $key => $bank) {
                                            foreach ($bank['data'] as $item) {

                                                if (!empty($item['ID_PLAT_KLIENT'])) {

                                                    if (!isset($id_p_k_to_id[$item['ID_PLAT_KLIENT']])) {

                                                        $stm_find_payment->execute([$item['ID_PLAT_KLIENT'], Authorization::getLoggedUserId()]);
                                                        $id_p_k_to_id[$item['ID_PLAT_KLIENT']] = $stm_find_payment->fetchColumn();
                                                    }
                                                    
                                                    $tmp_payment = $id_p_k_to_id[$item['ID_PLAT_KLIENT']];
                                                }

                                                $pdate = DateTime::createFromFormat('d.m.y H:i:s', $item['PDATE']);
                                                ?>
                                                <tr class="item-row">
                                                    <td class="align-center">
                                                        <?php
                                                            if ($tmp_payment !== false) {
                                                                ?>
                                                                <a href="<?= BASE_URL; ?>/cabinet/payments/details/<?= $tmp_payment; ?>/">
                                                                    #<?= $tmp_payment; ?>
                                                                </a>
                                                                <?php
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="border-bottom">
                                                        <label class="header header-big">
                                                            <strong class="green"><?= $item['NAME_PLAT']; ?></strong> <br>
                                                            <?= $item['NAME_FIRME']; ?>
                                                            (о.р. <?= $item['ABCOUNT']; ?>) <br>
                                                        </label>
                                                    </td>
                                                    <td class="border-bottom">
                                                        <span class="date-day"><?= $pdate->format('d/m/y'); ?></span><br>
                                                        <span class="date-time"><?= $pdate->format('H:i:s'); ?></span>
                                                    </td>
                                                    <td class="border-bottom">
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
                                                    <td class="border-bottom">
                                                        <?php
                                                            $summ = floatval(str_replace(",", ".", $item['SUMM']));
                                                            $summ = explode(',', $item['SUMM']);
                                                        ?>
                                                        <span class="item-summ <?= $class; ?>"><?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span></span>
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
            </form>
        </div>
        <?php
            if ($have_error) {
                ?><h2 class="big-error-message"><?= $error; ?></h2> <?php
            }
        ?>
