<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    $flat_id = $__route_result['values']['id'];
    
    try {
        $flatData = Flat::getUserFlatById($flat_id);

        if($flatData == null) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }
        
        $debt = new KomDebt();
        $debtData = $debt->getData($flatData['flat_id']);
        
        if (empty($debtData['list'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }

        $debtMonth = date("n", strtotime($debtData['dbegin']));

        if ($debtMonth == 1) {
            $previousMonth = 12;
            $previousYear = date("Y") - 1;
            $dateBegin = "1.".$previousMonth.".".$previousYear;
        } else {
            $previousMonth = $debtMonth - 1;
            $previousYear = date("Y");
            $dateBegin = "1.".$previousMonth.".".$previousYear;
        }
        
        $dateEnd = $debtData['dbegin'];

    } catch(Exception $e) {
        ?><h2 class="big-error-message"><?= $error; ?></h2> <?php
        return;
    }
?>

<form class="real-full-width-block" action="<?= BASE_URL; ?>/post/cabinet/object-item/paybill/" method="post">
    <table class="full-width-table datailbill-table no-border">
        <thead>
            <tr>
                <th class="first align-center counters-th" colspan="5">
                    Рахунок на <?= $debtData['date']; ?>
                </th>
            </tr>
            <tr>
                <th class="first align-center" colspan="5">
                    <span><?= $object['address']; ?></span><br>
                    Загальна площа: <b><?= $debtData['PL_OB']; ?> м.кв.</b>, опалювальна: <b><?= $debtData['PL_POL']; ?> м.кв.</b>, проживаючих: <b><?= $debtData['PEOPLE']; ?></b>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="bank-name title">
                <td class="first">
                    <div class="check-box-line">
                        <span id="check_all_services" class="niceCheck check-group-rule checked"><input onchange="checkAllServices($('#check_all_services-elem'));" id="check_all_services-elem" type="checkbox" checked="checked"></span>
                        <label onclick="$('#check_all_services').click();">
                            Назва послуги
                        </label>
                    </div>
                </td>
                <td style="white-space:nowrap;">Нараховано за<br><?= $MONTHS_NAME[$previousMonth]['ua']; ?>, грн</td>
                <td style="white-space:nowrap;">Сума боргу,<br>грн</td>
                <td style="white-space:nowrap;">Переплата,<br>грн</td>
                <td style="white-space:nowrap;">До сплати,<br>грн</td>
            </tr>
            <?php
                $counter = 0;

                foreach ($debtData['list'] as $key => $item) {
                    $counter++;
                    ?>
                    <tr class="item-row <?= ($counter % 2 == 0) ? 'even' : 'odd'; ?>">
                        <td class="first">
                            <div class="check-box-line">
                                <span class="niceCheck check-group checked" id="bill_item_<?= $key; ?>">
                                    <input checked="checked" onchange="selectService('bill_checkbox_<?= $key; ?>', 'inp_<?= $key; ?>');" type="checkbox" id="bill_checkbox_<?= $key; ?>" value="inp_<?= $key; ?>" name="items[]">
                                </span>
                                <label onclick="$('#bill_item_<?= $key; ?>').click();">
                                    <span><?= $item['name_plat']; ?></span>
                                    <br>
                                    <?= $item['firm_name']; ?>
                                    <?php
                                        if (!empty($item['counterData']['NAIM_LG'])) {
                                            ?>
                                            <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> <br>
                                            <span class="small">Льготы: <?= $item['counterData']['NAIM_LG']; ?>, <?= $item['counterData']['PROC_LG']; ?>% (кількість пільговиків: <?= $item['counterData']['KOL_LGOT']; ?>)</span>
                                            <?php
                                        }
                                    ?>
                                    <span class="small"><br><?= htmlspecialchars($item['FIO']); ?> (о.р.<?= $item['ABCOUNT']; ?>)</span> 
                                </label>
                            </div>
                            <?php
                                if ($item['counter'] != 0) {
                                    $item['to_pay'] = '0,00';

                                    foreach ($item['counterData']['counters'] as $counter) {
                                        ?>
                                        <div class="counter-data">
                                            <br>
                                            Показання лічильника №<?= $counter['COUNTER_NO']; ?> : <br>
                                            
                                            <div style="margin-top:5px; margin-bottom:5px;">
                                                <label for="old_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" style="width:100px; display:inline-block;">Попередні:</label>
                                                <input style="width:50px;" value="<?= $counter['OLD_VALUE']; ?>" id="old_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" type="number" min="0" maxlength="6" onkeypress="return checkForInt(event);" onkeyup="$(this).change();" onchange="recount_counter_summ('<?= $key; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');">
                                            </div>
                                            <div style="margin-bottom:5px;">
                                                <label for="inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" style="width:100px; display:inline-block;">Поточні:</label>
                                                <input min="<?= (int)$counter['OLD_VALUE']; ?>" class="text inp_<?= $key; ?>_new_count" type="number" id="inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" name="inp_<?= $key; ?>_new_count[]" maxlength="6" value="" style="width: 50px;" onkeypress="return checkForInt(event);" onkeyup="$(this).change();" onchange="recount_counter_summ('<?= $key; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');">
                                            </div>
                                            
                                            <input type="hidden" name="inp_<?= $key; ?>_old_count[]" value="<?= $counter['OLD_VALUE']; ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_count_number[]" value="<?= $counter['COUNTER_NO']; ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_abcounter[]" value="<?= $counter['ABCOUNTER']; ?>">
                                            До сплати: ( <div style="display:inline-block;" id="newval_counter_<?= $key; ?>_<?= $counter['COUNTER_NO']; ?>">нове&nbsp;значення</div>&nbsp;-&nbsp;<span id="oldval_counter_<?= $key; ?>_<?= $counter['COUNTER_NO']; ?>"><?= $counter['OLD_VALUE']; ?></span>)&nbsp;*&nbsp;<?= $item['counterData']['real_tarif']; ?>&nbsp;грн
                                        </div>
                                        <?php
                                    }
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                if ($item['SUMM_MONTH'] == '-') {
                                    echo '—';
                                } else {
                                    $summ = explode(',', $item['SUMM_MONTH']);
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                    <?php
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                if ($item['debt'] == '-') {
                                    echo '—';
                                } else {
                                    $summ = explode(',', $item['debt']);
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                    <?php
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                if ($item['over_pay'] == '-') {
                                    echo '—';
                                } else {
                                    $summ = explode(',', $item['over_pay']);
                                    ?>
                                    <span class="item-summ">
                                        <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                    </span>
                                    <?php
                                }
                            ?>
                        </td>
                        <td>
                            <input class="bill-summ-input txt num-short green bold form-txt-input" type="text" name="inp_<?= $key; ?>_sum" size="20" value="<?= $item['to_pay']; ?>" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $key; ?>">
                            <?php
                                // htmlspecialchars не делаем, так как эти данные уже должны быть обработаны
                                $tmp_value  =      $item['CODE_FIRME'];
                                $tmp_value .= '_'. $item['CODE_PLAT'];
                                $tmp_value .= '_'. $item['ABCOUNT'];
                                $tmp_value .= '_'. $item['PLAT_CODE'];
                                $tmp_value .= '_'. $item['NAME_BANKS'];
                                $tmp_value .= '_'. $item['BANK_CODE'];
                                $tmp_value .= '_'. $item['DBEGIN_XML'];
                                $tmp_value .= '_'. $item['DEND_XML'];
                                $tmp_value .= '_'. $item['FIO'];
                            ?>
                            <input type="hidden" name="inp_<?= $key; ?>_data" value="<?= $tmp_value; ?>">
                            <input type="hidden" name="inp_<?= $key; ?>_name_plat" value="<?= htmlspecialchars($item['name_plat'], ENT_QUOTES); ?>">
                            <input type="hidden" name="inp_<?= $key; ?>_firm_name" value="<?= htmlspecialchars($item['firm_name'], ENT_QUOTES); ?>">
                            <input type="hidden" name="inp_<?= $key; ?>_date_d" value="<?= htmlspecialchars($item['DATE_D'], ENT_QUOTES); ?>">
                            <input type="hidden" name="inp_<?= $key; ?>_id_pat" value="<?= htmlspecialchars($item['ID_PLAT'], ENT_QUOTES); ?>">
                        </td>
                    </tr>
                    <?php
                }
            ?>
            <tr class="total-summ-tr">
                <td class="first align-right" colspan="4">Усьго, грн:</td>
                <td class="total-sum" id="total_debt"><?= $debtData['full_dept']; ?></td>
            </tr>
            <tr>
                <td class="align-center" colspan="5">
                    <input type="hidden" name="dbegin" value="<?= $dateBegin; ?>">
                    <input type="hidden" name="dend" value="<?= $dateEnd; ?>">
                    <input type="hidden" name="flat_id" value="<?= $flat_id; ?>">
                    <button class="btn green bold big" id="pay_button">Сплатити</button>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<script>
$(document).ready(function(){
    $(".niceCheck").click(function() {
        changeCheck($(this), 'check-group');
    });
});
</script>
