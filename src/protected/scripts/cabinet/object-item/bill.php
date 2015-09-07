<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

    $flat_hash_id = $__route_result['values']['id'];
    
    try {
        $flatData = Flat::getUserFlatById($flat_hash_id);

        if($flatData == null) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }
            
        $debt = new KomDebt();
        $debtData = $debt->getData($flatData['flat_id']);
        
        // $this_year = substr($debtData['list'][0]['DBEGIN_XML'], 0, 4);
        // $this_month = (int)substr($debtData['list'][0]['DBEGIN_XML'], 5, 2);

        // $this_year = substr($debtData['dbegin'], strlen($debtData['dbegin'])-4);
        // $this_month = substr($debtData['dbegin'], strlen($debtData['dbegin'])-7, 2);
        
        // $debtData['date'] = '1 '.$monthsShort[$this_month].' '.$this_year;
        $_SESSION['debt_date'] = $debtData['date'];
        $_SESSION['bill'] = 1;
        
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
        $error = $e->getMessage();
    }
?>
<div class="gerc-fees">
    <p class="gerc_adress">
        <span><?= $flatData['address']; ?></span><br>
        Загальна площа: <b><?= $debtData['PL_OB']; ?> м.кв.</b>, опалювальна: <b><?= $debtData['PL_POL']; ?> м.кв.</b>, <b><?= $debtData['PEOPLE']; ?></b> проживаючих
    </p>
    <form class="gerc-fees-form" action="<?= BASE_URL; ?>/paybill/" method="post">
        <div>Рахунок на <?= $debtData['date']; ?></div><br>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" name="" onclick="checkAllServices(this);" checked="checked"/></th>
                    <th class="th-service">Назва послуги</th>
                    <th>Сума боргу, грн</th>
                    <th>Переплата, грн</th>
                    <th>До сплати, грн</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if (count($debtData['list']) > 0) {
                        foreach ($debtData['list'] as $key => $item) {
                            ?>
                            <tr class="<?= (is_int($key/2)) ? '' : 'grey'; ?>">
                                <?php
                                    if ($item['counter'] == 0) {
                                        ?>
                                        <td class="td-checkbox">
                                            <input type="checkbox" name="items[]" value="inp_<?= $key; ?>" checked="checked" onclick="selectService(this, 'inp_<?= $key; ?>', '<?= $key; ?>');"/></td>
                                        <td class="td-service">
                                            <span><?= $item['name_plat']; ?></span>
                                            
                                            <br>
                                            <?= $item['firm_name']; ?>
                                            <?php
                                                if($item['counterData']['NAIM_LG'])
                                                {
                                                    ?>
                                                    <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> 
                                                    <br>
                                                    <span class="small">Льготы: <?= $item['counterData']['NAIM_LG']; ?>, <?= $item['counterData']['PROC_LG']; ?>% (кількість пільговиків: <?= $item['counterData']['KOL_LGOT']; ?>)</span>
                                                    <?php
                                                }
                                            ?>
                                            <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> 
                                            </td>
                                        <td class="td-dept"><?= ($item['debt'] == '-') ? '<span class="empty">' . $item['debt'] . '</span>' : $item['debt']; ?></td>
                                        <td class="td-over"><?= ($item['over_pay'] == '-') ? '<span class="empty">' . $item['over_pay'] . '</span>' : $item['over_pay']; ?></td>
                                        <td class="td-pay">
                                            <input class="text" type="text" name="inp_<?= $key; ?>_sum" size="20" value="<?= $item['to_pay']; ?>" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $key; ?>"/>
                                            <input type="hidden" name="inp_<?= $key; ?>_data" value="<?= $item['CODE_FIRME']; ?>_<?= $item['CODE_PLAT']; ?>_<?= $item['ABCOUNT']; ?>_<?= $item['PLAT_CODE']; ?>_<?= $item['NAME_BANKS']; ?>_<?= $item['BANK_CODE']; ?>_<?= $item['DBEGIN_XML']; ?>_<?= $item['DEND_XML']; ?>_<?= $item['FIO']; ?>" />
                                            <input type="hidden" name="inp_<?= $key; ?>_date_d" value="<?= htmlspecialchars($item['DATE_D'], ENT_QUOTES); ?>" />
                                            <input type="hidden" name="inp_<?= $key; ?>_id_pat" value="<?= htmlspecialchars($item['ID_PLAT'], ENT_QUOTES); ?>" />
                                        </td>
                                        <?php
                                    } else {
                                        ?>
                                        <td class="td-checkbox">
                                            <input type="checkbox" name="items[]" value="inp_<?= $key; ?>" checked="checked" onclick="selectService(this, 'inp_<?= $key; ?>', '<?= $key; ?>');"/></td>
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
                                                    <span class="small">( <div style="display:inline-block;" id="newval_counter_<?= $key; ?>_<?= $counter['COUNTER_NO']; ?>">нове&nbsp;значення</div>&nbsp;-&nbsp;<?= $counter['OLD_VALUE']; ?> <?= ($item['counterData']['CODE_FIRME'] == '6070') ? 'кВт/ч' : 'м<sup>3</sup>'; ?> )&nbsp;*&nbsp;<?= $item['counterData']['real_tarif']; ?>&nbsp;<?= ($item['counterData']['CODE_FIRME'] == '6070') ? 'кВт/ч' : 'м<sup>3</sup>'; ?></span>
                                                    <span class="small"><br>Увага! Суму до сплати можна змінити, ввівши нове значення</span>
                                                    <?php
                                                }
                                            ?>
                                            </td>
                                        <td class="td-dept"><?= ($item['debt'] == '-') ? '<span class="empty">' . $item['debt'] . '</span>' : $item['debt']; ?></td>
                                        <td class="td-over"><?= ($item['over_pay'] == '-') ? '<span class="empty">' . $item['over_pay'] . '</span>' : $item['over_pay']; ?></td>
                                        <td class="td-pay">
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
                    }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="total" colspan="4">Усьго, грн:</td>
                    <td class="total-sum" id="total_debt"><?= $debtData['full_dept']; ?></td>
                </tr>
            </tfoot>
        </table>
        <div class="submit">
            <input type="hidden" name="dbegin" value="<?= $dateBegin; ?>">
            <input type="hidden" name="dend" value="<?= $dateEnd; ?>">
            <input disabled class="submit-button" id="pay_button" type="submit" value="Сплатити"/>
        </div>
    </form>
    <div class="clearr"></div>
</div>