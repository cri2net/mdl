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
        ?><div class="container" ><h2 class="big-error-message"><?= $e->getMessage(); ?></h2></div> <?php
        return;
    }
?>
<script>
    var new_counter_no = {};
</script>
<div class="container">
    <content>
        <div class="cabinet-settings object-item object-item-bill">
            <form class="real-full-width-block" action="<?= BASE_URL; ?>/post/cabinet/object-item/paybill/" method="post">
                <div class="thead-bg">
                    <div class="head-green"></div>
                    <div class="head-green-lighter"></div>
                    <div class="head-gray"></div>
                </div>
                <div class="table-responsive">
                    <table class="full-width-table datailbill-table no-border" id="data-table">
                        <thead>
                            <tr class="head-green">
                                <th class="first align-center" colspan="6">
                                    Рахунок за <?= $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small']; ?> <?= date('Y', $debtData['timestamp']); ?> р.
                                </th>
                            </tr>
                            <tr class="head-green-lighter">
                                <th>Обрати<br>все</th>
                                <th>Відкрити<br>лічильники</th>
                                <th colspan="4" class="align-right">
                                    <strong><span><?= $object['address']; ?></span><br>
                                    Загальна площа: <b><?= $debtData['PL_OB']; ?> м<sup>2</sup></b>, опалювальна: <b><?= $debtData['PL_POL']; ?> м<sup>2</sup></b>, проживаючих: <b><?= $debtData['PEOPLE']; ?></b>
                                    <?php
                                        if (isset($debtData['LGOTA']) && $debtData['LGOTA']) {
                                            ?>
                                            <br>Пільги: <?= $debtData['LGOTA']; ?>
                                            <?php
                                        }
                                    ?>
                                </th>
                            </tr>
                            <tr class="head-gray">
                                <th class="align-center">
                                    <label class="checkbox no-label gray">
                                        <input onchange="checkAllServices($('#check_all_services-elem'));" checked="checked" id="check_all_services-elem" type="checkbox" class="check-all"><span></span>
                                    </label>
                                </th>
                                <th class="align-center"><a class="counter counter-open counter-all"></a></th>
                                <th class="th-header">Назва послуги /<br>одержувач коштів</th>
                                <th>Заборгованість /<br>переплата, грн</th>
                                <th>Нараховано за<br><?= $MONTHS_NAME[$debtMonth]['ua']['small']; ?>, грн*</th>
                                <th>До сплати,<br>грн**</th>
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
                                        <td class="align-center">
                                            <?php
                                                if (!empty($item['counterData'])) {
                                                    ?>
                                                    <a class="counter counter-open"></a>
                                                    <?php
                                                }
                                            ?>
                                        </td>
                                        <td class="border-bottom">
                                            <label class="header">
                                                <?= $item['name_plat']; ?><br>
                                                <?= $item['firm_name']; ?><br>
                                                <?php
                                                    if (!empty($item['counterData']['NAIM_LG'])) {
                                                        ?>
                                                        <span class="small">(о.р.<?= $item['ABCOUNT']; ?>)</span> <br>
                                                        <span class="small">Льготи: <?= $item['counterData']['NAIM_LG']; ?>, <?= $item['counterData']['PROC_LG']; ?>% (кількість пільговиків: <?= $item['counterData']['KOL_LGOT']; ?>)</span>
                                                        <?php
                                                    }
                                                ?>
                                                <?= htmlspecialchars($item['FIO']); ?> (о.р.<?= $item['ABCOUNT']; ?>)
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
                                            <input <?= $attrs; ?> class="bill-summ-input txt num-short form-txt-input" type="text" name="inp_<?= $key; ?>_sum" size="20" value="<?= $item['to_pay']; ?>" onblur="bill_input_blur(this);" onfocus="bill_input_focus(this);" onchange="recalc();" onkeyup="recalc();" id="inp_<?= $key; ?>">
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
                                                <tr id="item-counter-<?= $key; ?>-0" data-number="<?= $key; ?>" class="item-counter item-counter-<?= $key; ?>">
                                                    <td colspan="6">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="counter-field">
                                                                    <input class="inp_<?= $key; ?>_new_count" type="text" id="inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" name="inp_<?= $key; ?>_new_count[]" maxlength="10" value="" onkeyup="checkForDouble(this);" onchange="recount_counter_summ('<?= $key; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');">
                                                                    <span class="edit"></span>
                                                                </div>
                                                                <div class="counter-label">поточні</div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="counter-field">
                                                                    <input name="inp_<?= $key; ?>_old_count[]" type="text" maxlength="10" onkeyup="checkForDouble(this);" onchange="recount_counter_summ('<?= $key; ?>', <?= $item['counterData']['real_tarif']; ?>, '<?= $counter['COUNTER_NO']; ?>');" id="old_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" value="<?= $counter['OLD_VALUE']; ?>">
                                                                    <span class="edit"></span>
                                                                </div>
                                                                <div class="counter-label">минулі</div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div class="counter-field">
                                                                    <input type="text" id="num_inp_<?= $key; ?>_new_count_<?= $counter['COUNTER_NO']; ?>" name="inp_<?= $key; ?>_abcounter[]" value="<?= $counter['ABCOUNTER']; ?>">
                                                                    <a data-id="<?= $counter['ABCOUNTER']; ?>" class="delete counter-delete" onclick="$('#item-counter-<?= $key; ?>-0').remove(); new_counter_no.k<?= $key; ?>--;">&times;</a>
                                                                </div>
                                                                <div class="counter-label">№ лічильника</div>
                                                            </div>
                                                        </div>
                                                        <input type="hidden" name="inp_<?= $key; ?>_count_number[]" value="<?= $counter['COUNTER_NO']; ?>">
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                            <tr id="new_counters_for_<?= $key; ?>" class="item-counter item-counter-<?= $key; ?>" data-number="<?= $key; ?>">
                                                <td colspan="6">
                                                    <div class="row">
                                                        <a class="add-new btn btn-lg btn-green" onclick="add_new_counters('<?= $key; ?>', '<?= $counter['ABCOUNTER']; ?>', <?= $item['counterData']['real_tarif']; ?>);"><span>додати лічильник</span></a>
                                                        <script>
                                                            new_counter_no.k<?= $key; ?> = <?= count($item['counterData']['counters']); ?>;
                                                        </script>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                    <?php
                                }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-summ">
                                <td class="align-right" colspan="6">
                                    Всього:<br><span id="total_debt"><?= $debtData['full_dept']; ?></span> &#8372;
                                </td>
                            </tr>
                            <tr>
                                <td class="align-right" colspan="6">
                                    <input type="hidden" name="dbegin" value="<?= $dateBegin; ?>">
                                    <input type="hidden" name="dend" value="<?= $dateEnd; ?>">
                                    <input type="hidden" name="flat_id" value="<?= $flat_id; ?>">
                                    <button class="btn btn-blue btn-md" id="pay_button">Сплатити</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>


                    <!-- <table class="full-width-table datailbill-table no-border hints-table">
                        <tbody>
                            <tr class="item-row">
                                <td class="first">
                                    <b class="hint-star">*</b> — з врахуванням пільг, субсидій та перерахунків <br>
                                    <b class="hint-star">**</b> — з врахуванням заборгованності <br>
                                </td>
                            </tr>
                        </tbody>
                    </table> -->
                </div>
            </form>
        </div>
    </content>
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
