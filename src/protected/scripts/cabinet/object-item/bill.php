<?php
    if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(PROTECTED_DIR . '/pages/cabinet/login.php');
    }

    define('IS_ONLINE_REP', true);

    $flat_id = $__route_result['values']['id'];
    
    try {
        $flatData = Flat::getUserFlatById($flat_id);

        if ($flatData == null) {
            throw new Exception(ERROR_NOT_FIND_FLAT);
        }
        
        $debt = new KomDebt();
        $plat_code = KomDebt::getFlatIdOrPlatcode($flatData['flat_id'], $flatData['plat_code'], $flatData['city_id']);
        $debtData = $debt->getData($plat_code);
        
        if (empty($debtData['list'])) {
            throw new Exception(ERROR_EMPTY_BILL);
        }

        $debtMonth = date("n", $debtData['timestamp']);
        $next_month = strtotime('next month', $debtData['timestamp']);

        if ($debtMonth == 1) {
            $previousMonth = 12;
            $previousYear = date("Y") - 1;
            $dateBegin = "1.".$previousMonth.".".$previousYear;
        } else {
            $previousMonth = $debtMonth - 1;
            $previousYear = date("Y");
            $dateBegin = "1.".$previousMonth.".".$previousYear;
        }
        
        $recalcData = $debt->getReCalc($flatData['flat_id'], date('01.m.Y', $debtData['timestamp'] + 86400 * 35));
        $have_recalc = !empty($recalcData);
        $dateEnd = $debtData['dbegin'];

    } catch (Exception $e) {
        ?><div class="container-fluid"><h2 class="big-error-message"><?= $e->getMessage(); ?></h2></div> <?php
        return;
    }
?>
<script>
    var new_counter_no = {};
</script>

        <div class="cabinet-settings object-item object-item-bill">
            <form class="real-full-width-block" action="<?= BASE_URL; ?>/post/cabinet/object-item/paybill/" method="post">
                <div class="row table-caption">
                    <div class="col-md-6 cal matchHeight">
                        <div>
                            <span class="fa fa-calendar"></span>
                            Рахунок за <?= $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small']; ?> <?= date('Y', $debtData['timestamp']); ?> р.  
                        </div>
                    </div>
                    <div class="col-md-6 right matchHeight">
                        <strong><span class="green"><?= $object['address']; ?></span><br>
                        <span>Загальна площа: <b><?= $debtData['PL_OB']; ?> м<sup>2</sup></b>, опалювальна: <b><?= $debtData['PL_POL']; ?> м<sup>2</sup></b>, проживаючих: <b><?= $debtData['PEOPLE']; ?></b>
                        <?php
                            if (isset($debtData['LGOTA']) && $debtData['LGOTA']) {
                                ?>
                                <br>Пільги: <?= $debtData['LGOTA']; ?>
                                <?php
                            }
                        ?>
                        </strong></span>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="table-responsive border-top">
                    <table class="full-width-table datailbill-table no-border" id="data-table">
                        <thead>
                        <!--
                            <tr class="head-green">
                                <th class="first align-center" colspan="7">

                                </th>
                            </tr>
                            <tr class="head-green-lighter">
                                <th>Обрати все</th>
                                <th>Відкрити лічильники</th>
                                <th colspan="5" class="align-right">
                                    
                                </th>
                            </tr>
                        -->
                            <tr class="head-gray">
                                <th class="align-center th-checkbox">
                                    <label class="checkbox no-label gray">
                                        <input onchange="checkAllServices($('#check_all_services-elem'));" checked="checked" id="check_all_services-elem" type="checkbox"><span></span>
                                    </label>
                                </th>
                                <!--<th class="align-center"><a class="counter counter-open counter-all"></a></th>-->
                                <th class="th-header">Назва послуги /<br>одержувач коштів</th>
                                <th>Заборгованість&nbsp;/<br>переплата, грн</th>
                                <th>Нараховано&nbsp;за<br><?= $MONTHS_NAME[$debtMonth]['ua']['small']; ?>, грн*</th>
                                <th style="white-space:nowrap;">Сплачено у<br><?= $MONTHS_WHEN[date('n', $next_month)]['ua']; ?> **</th>
                                <th style="white-space:nowrap; max-width: 166px;">До сплати,<br>грн ***</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $row_counter = 0;

                                foreach ($debtData['list'] as $key => $item) {
                                    $row_counter++;
                                    ?>

                                    <tr class="item-row" data-number="<?= $key; ?>">
                                        <td class="align-center">
                                            <label class="checkbox no-label green"><input checked="checked" id="bill_checkbox_<?= $key; ?>" value="inp_<?= $key; ?>" onchange="selectService('bill_checkbox_<?= $key; ?>', 'inp_<?= $key; ?>');" name="items[]" type="checkbox" class="check-toggle"><span></span></label>
                                        </td>
                                        <?php /*
                                        <td class="align-center">
                                            <?php
                                                if (!empty($item['counterData'])) {
                                                    ?>
                                                    <a class="counter counter-open"></a>
                                                    <?php
                                                }
                                            ?>
                                        </td>
                                        */
                                        ?>
                                        <td class="border-bottom">
                                            <label class="header">
                                                <strong class="green"><?= $item['name_plat']; ?><br></strong>
                                                <?= $item['firm_name']; ?><br>
                                                <?php
                                                    if (!empty($item['counterData']['NAIM_LG'])) {
                                                        ?>
                                                        <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> <br>
                                                        <span class="small">Льготи: <?= $item['counterData']['NAIM_LG']; ?>, <?= $item['counterData']['PROC_LG']; ?>% (кількість пільговиків: <?= $item['counterData']['KOL_LGOT']; ?>)</span>
                                                        <?php
                                                    }
                                                ?>
                                                (о.р.<?= $item['ABCOUNT']; ?>)
                                            </label>
                                        </td>
                                        <td class="border-bottom">
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
                                        <td class="border-bottom">
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

                                                if ($have_recalc && strstr($item['name_plat'], 'УТРИМАННЯ')) {

                                                    $summ = 0;
                                                    foreach ($recalcData as $tmp_item) {
                                                        foreach ($tmp_item['list'] as $list_item) {
                                                            $summ += $list_item['sum'];
                                                        }
                                                    }

                                                    // в xml есть поле "Всього по о/рахунку(по періоду)", где дублирутеся общая сумма.
                                                    // То есть надо на два поделить то, что выше в цикле просуммировано
                                                    $summ /= 2;
                                                    ?>
                                                    <span style="font-size: 28px; font-style: italic; color: #00b86c; font-family: Times; cursor: help;" title="Перерахунок <?= $summ; ?> грн">&nbsp;i</span>
                                                    <?php
                                                }
                                            ?>
                                        </td>
                                        <td class="border-bottom">
                                            <?php
                                                $summ = explode(',', $item['SUMM_PLAT']);
                                            ?>
                                            <span class="item-summ">
                                                <?= $summ[0]; ?><span class="small">,<?= $summ[1]; ?></span>
                                            </span>
                                        </td>
                                        <td class="border-bottom">
                                            <?php
                                                $attrs = '';
                                                if ($item['SUMM_OBL_PAY'] > 0) {
                                                    $attrs .= 'data-obl-pay="'. sprintf('%.2f', $item['SUMM_OBL_PAY']) .'" ';
                                                    $attrs .= 'title="Обов’язковий платіж по субсидії '. $item['SUMM_OBL_PAY'] .' грн" ';
                                                }

                                                // htmlspecialchars не делаем, так как эти данные уже должны быть обработаны
                                                $tmp_value  =      $item['ID_FIRME'];
                                                $tmp_value .= '_'. $item['CODE_PLAT'];
                                                $tmp_value .= '_'. $item['ABCOUNT'];
                                                $tmp_value .= '_'. $item['PLAT_CODE'];
                                                $tmp_value .= '_'. $item['NAME_BANKS'];
                                                $tmp_value .= '_'. $item['BANK_CODE'];
                                                $tmp_value .= '_'. $item['DBEGIN_XML'];
                                                $tmp_value .= '_'. $item['DEND_XML'];
                                                $tmp_value .= '_'. $item['FIO'];
                                            ?>
                                            <input <?= $attrs; ?> class="bill-summ-input txt form-txt-input" type="text" name="inp_<?= $key; ?>_sum" size="20" value="<?= $item['to_pay']; ?>" onblur="bill_input_blur(this);" onfocus="bill_input_focus(this);" onchange="recalc();" onkeyup="recalc(); return checkForDouble(this)" id="inp_<?= $key; ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_data" value="<?= $tmp_value; ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_name_plat" value="<?= htmlspecialchars($item['name_plat'], ENT_QUOTES); ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_firm_name" value="<?= htmlspecialchars($item['firm_name'], ENT_QUOTES); ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_code_firme" value="<?= htmlspecialchars($item['CODE_FIRME'], ENT_QUOTES); ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_date_d" value="<?= htmlspecialchars($item['DATE_D'], ENT_QUOTES); ?>">
                                            <input type="hidden" name="inp_<?= $key; ?>_id_pat" value="<?= htmlspecialchars($item['ID_PLAT'], ENT_QUOTES); ?>">
                                        </td>
                                    </tr>
                                    <?php
                                        if ($item['counter'] != 0) {

                                            // $item['to_pay'] = str_replace('.', ',', sprintf('%.2f', $item['SUMM_OBL_PAY']));

                                            foreach ($item['counterData']['counters'] as $counter) {
                                                
                                                ?>
                                                <tr class="item-counter">
                                                    <td></td>
                                                    <td colspan="6">
                                                        <div class="counter-container counter-container-<?= $key; ?>">
                                                            <div class="row row-counter item-counter-<?= $key; ?>"  id="item-counter-<?= $key; ?>-<?= $counter['COUNTER_NO']; ?>" data-number="<?= $key; ?>">
                                                                <div class="col-md-12">
                                                                    <div class="counter-field">
                                                                        <label>поточні</label>
                                                                        <input class="inp_<?= $key; ?>_new_count" type="text" id="inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" name="inp_<?= $key; ?>_new_count[]" maxlength="10" value="" onkeyup="checkForDouble(this);" onchange="recount_counter_summ('<?= $key; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');">
                                                                    </div>
                                                                    <div class="counter-field">
                                                                        <label>минулі</label>
                                                                        <input name="inp_<?= $key; ?>_old_count[]" type="text" maxlength="10" onkeyup="checkForDouble(this);" onchange="recount_counter_summ('<?= $key; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');" id="old_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" value="<?= $counter['OLD_VALUE']; ?>">
                                                                    </div>
                                                                    <div class="counter-field">
                                                                        <label>№ лічильника</label>
                                                                        <input type="text" id="num_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" name="inp_<?= $key; ?>_abcounter[]" value="<?= $counter['ABCOUNTER']; ?>">
                                                                        <a data-id="<?= $counter['ABCOUNTER']; ?>" class="delete counter-delete" onclick="$('#item-counter-<?= $key; ?>-<?= $counter['COUNTER_NO']; ?>').remove(); new_counter_no.k<?= $key; ?>--;">&times;</a>
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" name="inp_<?= $key; ?>_count_number[]" value="<?= $counter['COUNTER_NO']; ?>">
                                                            </div>
                                                            <div class="row row-add-counter">
                                                                <div class="col-lg-12">
                                                                    <a class="add-new btn btn-xs btn-green-bordered" onclick="add_new_counters('<?= $key; ?>', '<?= $counter['ABCOUNTER']; ?>', <?= $item['counterData']['real_tarif']; ?>);"><span class="fa fa-plus"></span> додати лічильник</a>
                                                                    <script> new_counter_no.k<?= $key; ?> = <?= count($item['counterData']['counters']); ?>; </script>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php
                                            }/*
                                            ?>
                                            <tr id="new_counters_for_<?= $key; ?>" class="item-counter item-counter-<?= $key; ?>" data-number="<?= $key; ?>">
                                                <td></td>
                                                <td colspan="6">

                                                </td>
                                            </tr>
                                            <?php
                                            */
                                        }
                                    ?>
                                    <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>                    
                    <div class="tfoot row total-summ">
                        <div class="col-lg-12">
                            Всього: <span id="total_debt"><?= $debtData['full_dept']; ?></span> &#8372;
                        </div>
                        <div class="col-lg-12">
                            <input type="hidden" name="dbegin" value="<?= $dateBegin; ?>">
                            <input type="hidden" name="dend" value="<?= $dateEnd; ?>">
                            <input type="hidden" name="flat_id" value="<?= $flat_id; ?>">
                            <button class="btn" id="pay_button"><span class="fa fa-check"></span> Сплатити</button>
                        </div>
                    </div>

                    <table class="full-width-table datailbill-table no-border hints-table">
                        <tbody>
                            <tr class="item-row">
                                <td class="first">
                                    <b class="hint-star">*</b> — з врахуванням пільг, субсидій та перерахунків поточного періоду<br>
                                    <b class="hint-star">**</b> — сума інформативна та не бере участь у загальній калькуляції за послугою; оновлюється протягом 1-3 банківських днів після сплати <br>
                                    <b class="hint-star">***</b> — з врахуванням заборгованності <br>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </form>
        </div>

<div class="modal fade" id="modalCounterConfirm" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Видалити лічильник?</h3>
      </div>
      <div class="modal-body">
        <p>видалений лічильник неможливо відновити</p>
      </div>
      <div class="modal-footer">
        <div class="row">
            <div class="col-sm-6">
                <button type="button" class="btn btn-green btn-full btn-md" data-dismiss="modal">Назад</button>
            </div>
            <div class="col-sm-6">
                <button type="button" id="counter-delete-confirm" data-id="" class="btn btn-green-lighter btn-full btn-md">Видалити</button>
            </div>
        </div>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
