<h1>Розрахунок за показаннями квартирних<br>приладів обліку</h1>

<div class="calculator">
    <?php
        foreach ($_POST as $key => $value) {
            $$key = $value;
        }
        
        $Err = false;

        if (!isset($KPR)) $KPR = 1;
        else $KPR = (int)$KPR;
        
        if (!isset($KLG)) $KLG = 1;
        else $KLG = (int)$KLG;
        
        $KLLGi = 0;
        for ($i=0; $i<$KLG; $i++) {
            $KLLGi = $KLLGi + $KLLG[$i];
        }
        
        if (!isset($F_GW) || ($F_GW == '')) $F_GW = 0;
        //else $F_GW = (int)$F_GW;
        if ($N_HW == '4.0') $F_GW = 0;
        
        
        
        if (!empty($_POST)) {
        for ($i=0; $i<$KLG; $i++) {
            if (!isset($KLLG[$i]) || empty($KLLG[$i]) || ($KLLG[$i] < 1)) $Err = true;
            if ($PRO[$i] == '%') $Err = true;
        }
        if ($N_HW == 'm3') $Err = true;
        if (($F_GW != 0) && ($N_GW == 'm3')) $Err = true;
        if (($F_GW != 0) && (($T_GW == 't') || ($T_GW == '')  || ($T_GW == 0)  || !isset($T_GW)))  $Err = true;
        
        if ($Err == False) // Считаем
        {
            if (!isset($F_HW) || ($F_HW == '')) $F_HW = 0;
            if (!isset($T_HW) || ($T_HW == '') || ($T_HW == 0)) $T_HW = 10.24;
            
            if (!isset($F_GW) || ($F_GW == '')) $F_GW = 0;
            if (!isset($T_ST) || ($T_ST == '') || ($T_ST == 0)) $T_ST = 4.82;
            
            
            $HW = 0;
            $Count = 0;
            $D_HW = $F_HW / $KPR;
            if ($D_HW <= $N_HW) {
                for ($i=0; $i<$KLG; $i++) { $HW = $HW + ($D_HW * $KLLG[$i] * $T_HW * (100 - (int)str_replace('%', '', $PRO[$i])) / 100); $Count = $Count + $KLLG[$i]; }
                $HW = $HW + $D_HW * ($KPR - $Count) * $T_HW;
            }
            else {
                $R_HW = ($D_HW-$N_HW);
                for ($i=0; $i<$KLG; $i++) { $HW = $HW + ($N_HW * $KLLG[$i] * $T_HW * (100 - (int)str_replace('%', '', $PRO[$i])) / 100); $Count = $Count + $KLLG[$i]; }
                $HW = $HW + $R_HW * $Count * $T_HW + ($D_HW * ($KPR - $Count) * $T_HW);
            }
            
            $GW = 0;
            $D_GW = $F_GW / $KPR;
            if ($D_GW <= $N_GW) {
                for ($i=0; $i<$KLG; $i++) { $GW = $GW + ($D_GW * $KLLG[$i] * $T_GW * (100 - (int)str_replace('%', '', $PRO[$i])) / 100); }
                $GW = $GW + $D_GW * ($KPR - $Count) * $T_GW;
            }
            else {
                $R_GW = ($D_GW-$N_GW);
                for ($i=0; $i<$KLG; $i++) { $GW = $GW + ($N_GW * $KLLG[$i] * $T_GW * (100 - (int)str_replace('%', '', $PRO[$i])) / 100); }
                $GW = $GW + $R_GW * $Count * $T_GW + ($D_GW * ($KPR - $Count) * $T_GW);
            }

            $ST = 0;
            if ($F_GW != 0) {
            if ($D_GW <= $N_GW) {
                for ($i=0; $i<$KLG; $i++) { $ST = $ST + ($D_GW * $KLLG[$i] * $T_ST * (100 - (int)str_replace('%', '', $PRO[$i])) / 100); }
                $ST = $ST + $D_GW * ($KPR - $Count) * $T_ST;
            }
            else {
                for ($i=0; $i<$KLG; $i++) { $ST = $ST + ($N_GW * $KLLG[$i] * $T_ST * (100 - (int)str_replace('%', '', $PRO[$i])) / 100); }
                $ST = $ST + $R_GW * $Count * $T_ST + ($D_GW * ($KPR - $Count) * $T_ST);
            }
            }
        
        }
        else {
        }
        $HW = round($HW, 2);
        $GW = round($GW, 2);
        $ST = round($ST, 2);    

            ?>
            <div class="result-block">
                <table class="no-border">
                    <tr>
                        <td class="title green p50 lbw">Результати розрахунків 
                            <?php
                                if ($Err) echo "<strong><font color="#ff0000">(Розрахунки не здійснено!)</font></strong>";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="data rbw">
                            <table class="no-border">
                                <tr>
                                    <td>Холодне водопостачання та водовідведення</td>
                                    <td>
                                        <?
                                        if ($Err) echo "Розрахунок не здійснено";
                                        else echo "<b>$HW грн</b>";
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Гаряче водопостачання</td>
                                    <td>
                                        <?
                                        if ($Err) echo "Розрахунок не здійснено";
                                        else echo "<b>$GW грн</b>";
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Водовідведення гарячої води</td>
                                    <td>
                                        <?
                                        if ($Err) echo "Розрахунок не здійснено";
                                        else echo "<b>$ST грн</b>";
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <? if ($Err) { ?>
                <div class="inner alert">
                    <h4>Невірно введено дані:</h3>
                    <br>
                    <? if ($KLLGi > $KPR) { $Err = true; ?>
                    Загальна кількість мешканців менша ніж наведена кількість пільговиків.<br>
                    <? } elseif (!isset($KLG) || ($KLG < 0)) { $Err = true; ?>
                    Не вказано кількість пільг, які надаються по даному особовому рахунку.<br>
                    <? } 
                    for ($iii = 1; $iii <= $KLG; $iii++) {
                        if ((($KLG > 0) && ($Err) && ($KLLG[$iii] < 1)) || (($PRO[$iii] == '%') && ($Err))) echo "Пільга $iii - незаповнено (<i>або заповнено неповністю</i>)<br>";
                    }
                    if (($N_HW == 'm3') && ($Err)) echo "Незаповнено холодне водопостачання та водовідведення<br>";
                    if (($N_GW == 'm3') && ($Err) && ($F_GW != 0)) echo "Незаповнено гаряче водопостачання (<i>норма споживання на одну особу</i>)<br>";
                    if ((($T_GW == 't') || ($T_GW == '')  || ($T_GW == 0)  || !isset($T_GW)) && ($Err) && ($F_GW != 0)) echo "Незаповнено гаряче водопостачання (<i>тариф за 1 м3 гарячої води</i>)<br>";
                    ?>
                </div>
                <? } ?>
            </div>
            <?php
        }
    ?>
    <form action="<?= BASE_URL; ?>/calc-devices/" method="post">
        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-notepad.png" alt="" />Базовi данi</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-people.png" alt="" /></div>
                <div class="col-label">Кількість мешканців, які<br>користуються послугами</div>
                <div class="col-input">
                    <input name="KPR" type="text" class="txt num-short green bold s24 form-txt-input" value="<?= $KPR; ?>" maxlength="2" onkeypress="return isNumberKey(event);" />
                        <? if ($KLLGi > $KPR) { ?>
                            <img src="/style/exclamation.png" width="16" height="16" alt="" border="0"/>
                        <? } ?>
                </div>
            </div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
                <div class="col-label">Кількість пільг, які надаються<br>по даному особовому рахунку</div>
                <div class="col-input">
                    <input type="text" maxlength="1" onkeypress="return isNumberKey(event);" onkeyup="$(this).change();" value="<?= ($KLG) ? $KLG : 1; ?>" id="KLG" name="KLG" class="txt num-short green bold s24 form-txt-input" />
                </div>
            </div>
            <div id="calc-kllg-subblocks">
                <div class="calc-subblock">
                    <div class="item-row">
                        <div class="col-label">Кількість пільговиків, які користуються<br>{{nth}}-ю пільгою</div>
                        <div class="col-input">
                            <input type="text" class="txt num-short green bold s24 form-txt-input" name="KLLG[]"  maxlength="1" onkeypress="return isNumberKey(event);" />
                        </div>
                    </div>
                    <div class="item-row">
                        <div class="col-label">Відсоток знижки для {{nth}}-ї пільги</div>
                        <div class="col-input">
                            <select class="dropdown" name="PRO[]">
                                <option value="%">Оберiть %</option>
                                <option value="100%">100%</option>
                                <option value="75%">75%</option>
                                <option value="50%">50%</option>
                                <option value="25%">25%</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-cold-water.png" alt="" />Холодне водопостачання та водовідведення</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
                <div class="col-label"><? if (($N_HW == 'm3') && ($Err)) { ?><img src="/style/exclamation.png" width="16" height="16" alt="" border="0"/> <? } ?>Норма споживання на одну особу <span class="q tooltip" title="• за наявності централізованого постачання гарячої води – 2,4 м3<br>• за відсутності централізованого постачання гарячої води – 4,0 м3<br><br>(<i>Норми діють з 01.10.2014 року</i>)"></span></div>
                <div class="col-input">
                        <select class="dropdown" name="N_HW" onchange="if (this.selectedIndex == 2) { document.getElementById('F_GW').value = '0'; document.getElementById('N_GW').selectedIndex=0; }">
                            <option <? if ($N_HW == 'm3') echo "selected"; ?> value="m3">Оберiть м?</option>
                            <option <? if ($N_HW == '2.4') echo "selected"; ?> value="2.4">2,4 м?</option>
                            <option <? if ($N_HW == '4.0') echo "selected"; ?> value="4.0">4,0 м?</option>
                        </select>
                </div>
            </div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-cube.png" alt="" /></div>
                <div class="col-label">
                    Кількість спожитих м³<br>
                    <span class="comment">
                    за показаннями засобів обліку холодної води 
                    (якщо встановлено декілька засобів обліку 
                    наводиться сумарна кількість по всіх засобах обліку)
                    </span>
                </div>
                <div class="col-input">
                    <input type="text" name="F_HW" value="<?= "$F_HW"; ?>" maxlength="7" onkeypress="return isNumberKeyPlusDot(event)" class="txt num-short green bold s24 form-txt-input" />
                </div>
            </div>

            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
                <div class="col-label">Тариф за 1 м³ холодної води та<br>водовідведення, грн <span class="q tooltip" title="Можна вносити інше значення тарифу"></span></div>
                <div class="col-input">
                    <input type="text" class="txt num-short green bold s24 form-txt-input" name="T_HW" value="<?= (!isset($T_HW) || ($T_HW == '') || ($T_HW == 0)) ? "10.24" : "$T_HW"; ?>" maxlength="7" />
                </div>
            </div>
        </div>


        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-hot-water.png" alt="" />Гаряче водопостачання</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
                <div class="col-label"><? if (($N_GW == 'm3') && ($Err) && ($F_GW != 0)) { ?><img src="/style/exclamation.png" width="16" height="16" alt="" border="0"/> <? } ?>Норма споживання на одну особу <span class="q tooltip" title="• за наявності централізованого постачання гарячої води – 1,6 м3<br><br>(<i>Норми діють з 01.10.2014 року</i>)"></span></div>
                <div class="col-input">
                    <select class="dropdown" id="N_GW" name="N_GW">
                        <option <? if ($N_GW == 'm3') echo "selected"; ?> value="m3">Оберiть м?</option>
                        <option <? if ($N_GW == '1.6') echo "selected"; ?> value="1.6">1,6 м?</option>
                    </select>
                </div>
            </div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-cube.png" alt="" /></div>
                <div class="col-label">
                    Кількість спожитих м³<br>
                    <span class="comment">
                    за показаннями засобів обліку гарячої води (якщо встановлено декілька засобів обліку наводиться сумарна кількість по всіх засобах обліку)
                    </span>
                </div>
                <div class="col-input">
                    <input type="text" class="txt num-short green bold s24 form-txt-input" id="F_GW" name="F_GW" value="<?= $F_GW; ?>" maxlength="7" onkeypress="return isNumberKeyPlusDot(event)" />
                </div>
            </div>

            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
                <div class="col-label"><? if ((($T_GW == 't') || ($T_GW == '')  || ($T_GW == 0)  || !isset($T_GW)) && ($Err) && ($F_GW != 0)) { ?><img src="/style/exclamation.png" width="16" height="16" alt="" border="0"/> <? } ?>Тариф за 1 м? гарячої води, грн</div>
                <div class="col-input">
                    <input type="text" class="txt num-short green bold s24 form-txt-input" id="T_GW" name="T_GW" value="<?= $T_GW; ?>" maxlength="7" onkeypress="return isNumberKeyPlusDot(event)" />
                </div>
            </div>
        </div>
        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-hot-water-out.png" alt="" />Водовідведення гарячої води</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
                <div class="col-label">Тариф за 1 м³ водовідведення гарячої води, грн <span class="q tooltip" title="• за умови підключення рушникосушильників<br>  до систем гарячого водопостачання<br>  і виконавцем послуг є ПАТ «АК «Київенерго» - 40,92<br>• за умови відсутності підключення<br>  рушникосушильників до систем гарячого водопостачання<br>  і виконавцем послуг є ПАТ «АК «Київенерго» - 37,91<br>• за умови підключення рушникосушильників<br>  до систем гарячого водопостачання<br>  і виконавцем послуг є ТОВ «ЄВРО-РЕКОНСТРУКЦІЯ» - 39,89<br>• за умови відсутності підключення рушникосушильників<br>  до систем гарячого водопостачання<br>  і виконавцем послуг є ТОВ «ЄВРО-РЕКОНСТРУКЦІЯ» - 36,97<br><br>• за умови підключення рушникосушильників<br>  до систем гарячого водопостачання<br>  і виконавцем послуг є ТОВ «Теплопостачасервіс» - 35,87<br>• за умови відсутності підключення рушникосушильників<br>  до систем гарячого водопостачання<br>  і виконавцем послуг є ТОВ «Теплопостачасервіс» - 33,30" ></span></div>
                <div class="col-input">
                    <input type="text" class="txt num-short green bold s24 form-txt-input" name="T_ST" value="<?= (!isset($T_ST) || ($T_ST == '') || ($T_ST == 0)) ? '4.82' : $T_ST; ?>" maxlength="7" />
                </div>
            </div>
        </div>
        <button class="btn green bold">Розрахувати</button> 
    </form>
</div>
<script>
    $(document).ready(function(){
        calc_kllg_subblock_html = $('#calc-kllg-subblocks').html();
        
        $("#KLG").change(function(){
            var val = parseInt($("#KLG").val());
            if (isNaN(val)) {
                val = 0;
            }
            var html = '';
            for (var i = 0; i < val; i++) {
                html += calc_kllg_subblock_html.split('{{nth}}').join((i + 1).toString());
            };
            $('#calc-kllg-subblocks').html(html);
        });
        $("#KLG").change();
    });
</script>