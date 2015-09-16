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
                <th class="first align-center counters-th" colspan="4">
                    Рахунок на <?= $debtData['date']; ?>
                </th>
            </tr>
            <tr>
                <th class="first align-center" colspan="4">
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
                        <?php
                            if ($item['counter'] == 0) {
                                ?>
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
                                                if ($item['counterData']['NAIM_LG']) {
                                                    ?>
                                                    <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> <br>
                                                    <span class="small">Льготы: <?= $item['counterData']['NAIM_LG']; ?>, <?= $item['counterData']['PROC_LG']; ?>% (кількість пільговиків: <?= $item['counterData']['KOL_LGOT']; ?>)</span>
                                                    <?php
                                                }
                                            ?>
                                            <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> 
                                        </label>
                                    </div>
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
                                    <input class="bill-summ-input txt num-short green bold form-txt-input" type="text" name="inp_<?= $key; ?>_sum" size="20" value="<?= $item['to_pay']; ?>" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $key; ?>"/>
                                    <input type="hidden" name="inp_<?= $key; ?>_data" value="<?= $item['CODE_FIRME']; ?>_<?= $item['CODE_PLAT']; ?>_<?= $item['ABCOUNT']; ?>_<?= $item['PLAT_CODE']; ?>_<?= $item['NAME_BANKS']; ?>_<?= $item['BANK_CODE']; ?>_<?= $item['DBEGIN_XML']; ?>_<?= $item['DEND_XML']; ?>_<?= $item['FIO']; ?>" />
                                    <input type="hidden" name="inp_<?= $key; ?>_date_d" value="<?= htmlspecialchars($item['DATE_D'], ENT_QUOTES); ?>" />
                                    <input type="hidden" name="inp_<?= $key; ?>_id_pat" value="<?= htmlspecialchars($item['ID_PLAT'], ENT_QUOTES); ?>" />
                                </td>
                                <?php
                            } else {
                                ?>
                                <td class="first">
                                    <input type="checkbox" name="items[]" value="inp_<?= $key; ?>" checked="checked" onclick="selectService(this, 'inp_<?= $key; ?>', '<?= $key; ?>');"/>
                                </td>
                                <td class="td-service">
                                    <span><?= $item['name_plat']; ?></span><br>
                                    <?= $item['firm_name']; ?>
                                    <?php
                                        if ($item['counterData']['NAIM_LG']) {
                                            ?>
                                            <br>
                                            <span class="small">Пільги: <?= $item['counterData']['NAIM_LG']; ?>, <?= $item['counterData']['PROC_LG']; ?>% (кількість пільговиків: <?= $item['counterData']['KOL_LGOT']; ?>)</span>
                                            <?php
                                        }

                                        foreach ($item['counterData']['counters'] as $counter) {
                                            ?>
                                            <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span><br> 
                                            <span class="small">Попередні показання лічильника №<?= $counter['COUNTER_NO']; ?> : <span id="old_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>"><?= $counter['OLD_VALUE']; ?></span></span><br>
                                            <span class="small">Поточні показання лічильника №<?= $counter['COUNTER_NO']; ?>:</span>
                                            <input class="text inp_<?= $key; ?>_new_count" type="text" id="inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" name="inp_<?= $key; ?>_new_count[]" size="20" maxlength="6" value="" style="width: 50px; text-align: right;padding-right:2px;" onkeypress="return checkForInt(event);" onkeyup="recount_counter_summ('<?= $key; ?>', '<?= $counter['OLD_VALUE']; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');" /><br>
                                            <input type="hidden" name="inp_<?= $key; ?>_old_count[]" value="<?= $counter['OLD_VALUE']; ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_count_number[]" value="<?= $counter['COUNTER_NO']; ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_abcounter[]" value="<?= $counter['ABCOUNTER']; ?>">
                                            <span class="small">( <div style="display:inline-block;" id="newval_counter_<?= $key; ?>_<?= $counter['COUNTER_NO']; ?>">нове&nbsp;значення</div>&nbsp;-&nbsp;<?= $counter['OLD_VALUE']; ?> <?= ($item['counterData']['CODE_FIRME'] == '6070') ? 'кВт/ч' : 'м<sup>3</sup>'; ?> )&nbsp;*&nbsp;<?= $item['counterData']['real_tarif']; ?>&nbsp;м<sup>3</sup></span>
                                            <span class="small"><br>Увага! Суму до сплати можна змінити, ввівши нове значення</span>
                                            <?php
                                        }
                                    ?>
                                    </td>
                                <td><?= ($item['debt'] == '-') ? '<span class="empty">' . $item['debt'] . '</span>' : $item['debt']; ?></td>
                                <td><?= ($item['over_pay'] == '-') ? '<span class="empty">' . $item['over_pay'] . '</span>' : $item['over_pay']; ?></td>
                                <td>
                                    <input class="text" type="text" name="inp_<?= $key; ?>_sum" size="20" value="0,00" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $key; ?>"/>
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
                                    <input type="hidden" name="inp_<?= $key; ?>_data" value="<?= $tmp_value; ?>" />
                                    <input type="hidden" name="inp_<?= $key; ?>_date_d" value="<?= htmlspecialchars($item['DATE_D'], ENT_QUOTES); ?>" />
                                    <input type="hidden" name="inp_<?= $key; ?>_id_pat" value="<?= htmlspecialchars($item['ID_PLAT'], ENT_QUOTES); ?>" />
                                </td>
                                <?php
                            }
                        ?>
                    </tr>
                    <?php
                }
            ?>
            <tr class="total-summ-tr">
                <td class="first align-right" colspan="3">Усьго, грн:</td>
                <td class="total-sum" id="total_debt"><?= $debtData['full_dept']; ?></td>
            </tr>
            <tr>
                <td class="align-center" colspan="4">
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
