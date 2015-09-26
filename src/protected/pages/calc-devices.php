<h1>Розрахунок за показаннями квартирних<br>приладів обліку</h1>

<div class="calculator">
    <form action="#" method="post">
        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-notepad.png" alt="" />Базовi данi</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-people.png" alt="" /></div>
                <div class="col-label">Кількість мешканців, які<br/>користуються послугами</div>
                <div class="col-input">
                    <input name="KPR" type="text" class="txt num-short green bold s24 form-txt-input" value="<?= $KPR; ?>" maxlength="2" onkeypress="return isNumberKey(event);" />
                </div>
            </div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
                <div class="col-label">Кількість пільг, які надаються<br/>по даному особовому рахунку</div>
                <div class="col-input">
                    <input type="text" maxlength="1" onkeypress="return isNumberKey(event);" value="<?= $KLG; ?>" id="KLG" name="KLG" class="txt num-short green bold s24 form-txt-input" />
                </div>
            </div>
            <div class="calc-subblock">
                <div class="item-row">
                    <div class="col-label">Кількість пільговиків, які користуються<br/>1-ю пільгою</div>
                    <div class="col-input">
                        <input type="text" class="txt num-short green bold s24 form-txt-input" name="KLLG[]" value="<?= $KLLG[0]; ?>" maxlength="1" onkeypress="return isNumberKey(event);" />
                    </div>
                </div>
                <div class="item-row">
                    <div class="col-label">Кількість пільговиків, які користуються<br/>1-ю пільгою</div>
                    <div class="col-input">
                        <select class="dropdown" name="PRO[]">
                            <option>Оберiть %</option>
                            <option>100%</option>
                            <option>75%</option>
                            <option>50%</option>
                            <option>25%</option>
                        </select>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-cold-water.png" alt="" />Холодне водопостачання та водовідведення</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
                <div class="col-label">Норма споживання на одну особу <span class="q tooltip" title="• за наявності централізованого постачання гарячої води – 2,4 м3<br>• за відсутності централізованого постачання гарячої води – 4,0 м3<br><br>(<i>Норми діють з 01.10.2014 року</i>)"></span></div>
                <div class="col-input">
                        <select class="dropdown" name="N_HW" onchange="if (this.selectedIndex == 2) { document.getElementById('F_GW').value = '0'; document.getElementById('N_GW').selectedIndex=0; }">
                            <option value="m3">Оберiть м³</option>
                            <option value="2.4">2,4 м³</option>
                            <option value="4.0">4,0 м³</option>
                        </select>
                </div>
            </div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-cube.png" alt="" /></div>
                <div class="col-label">
                    Кількість спожитих м³<br/>
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
                <div class="col-label">Тариф за 1 м³ холодної води та<br/>водовідведення, грн <span class="q tooltip" title="Можна вносити інше значення тарифу"></span></div>
                <div class="col-input">
                    <input type="text" class="txt num-short green bold s24 form-txt-input" name="T_HW" value="<?= (!isset($T_HW) || ($T_HW == '') || ($T_HW == 0)) ? "10.24" : "$T_HW"; ?>" maxlength="7" />
                </div>
            </div>
        </div>








        <div class="calc-block">
            <div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-hot-water.png" alt="" />Гаряче водопостачання</div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
                <div class="col-label">Норма споживання на одну особу <span class="q tooltip" title="• за наявності централізованого постачання гарячої води – 1,6 м3<br><br>(<i>Норми діють з 01.10.2014 року</i>)"></span></div>
                <div class="col-input">
                    <select class="dropdown" id="N_GW" name="N_GW">
                        <option value="m3">Оберiть м³</option>
                        <option value="1.6">1,6 м³</option>
                    </select>
                </div>
            </div>
            <div class="item-row">
                <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-cube.png" alt="" /></div>
                <div class="col-label">
                    Кількість спожитих м³<br/>
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
                <div class="col-label">Тариф за 1 м³ гарячої води, грн</div>
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
