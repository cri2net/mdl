<h1>Розрахунок розміру обов’язкового платежу</h1>
<div class="calculator">
    <?php
        foreach ($_POST as $key => $value) {
            $$key = $value;
        }

        if (!isset($KL) || ($KL == 0) || ($KL == '')) $KL = 1;
        else $KL = (int)$KL;
        
        if (!isset($DOHOD) || ($DOHOD == 0) || ($DOHOD == '')) $DOHOD = '0';
        else $DOHOD = (int)$DOHOD;
        
        if (!empty($_POST)) {
            $Err = false; $Err_BIG_DOHOD = false;
            
            if ((!isset($DOHOD) || ($DOHOD < 1))) $Err = true;
            if ((!isset($KL) || ($KL < 1))) $Err = true;
                
            if ($Err == False) // Считаем
            {
                $RES  = round( $DOHOD / $KL / 1330 / 2 * 15 , 2 ); // 1176 --> 1330
                $RESP = round( $DOHOD / 100 * $RES , 2 );
                
                if (($RES > 100) || ($DOHOD / $KL > 3000)){
                    $Err_BIG_DOHOD = true;
                    $RES  = "";
                    $RESP = "";
                }
            }
        
            ?>
            <div class="result-block">
                <table class="no-border">
                    <tr>
                        <td class="title blue p50 rbw">Базовi данi</td>
                        <td class="title green p50 lbw">Розрахованi данi</td>
                    </tr>
                    <tr>
                        <td class="data rbw">
                            <table class="no-border">
                                <tr>
                                    <td>Сукупний дохід сім’ї</td>
                                    <td><b><?=$DOHOD?> грн</b></td>
                                </tr>
                                <tr>
                                    <td>Кількість осіб</td>
                                    <td><b><?=$KL?></b></td>
                                </tr>
                            </table>
                        </td>
                        <td class="data lbw">
                            <table class="no-border">
                                <tr>
                                    <td>Частка обов’язкової плати</td>
                                    <td><b class="green"><?=$RES?>%</b></td>
                                </tr>
                                <tr>
                                    <td>Обов’язкова плата за ЖКП</td>
                                    <td><b class="green"><?=$RESP?> грн</b></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="inner alert">
                <? if (($Err == False) && ($Err_BIG_DOHOD == False)) { ?>
                    <h4>Увага! Усі розрахунки є приблизними і не є підставою для призначення субсидії!</h3>
                    <br>
                    <p>
                    Якщо розрахована за Вашими даними обов’язкова плата (тобто сума, яку має сплачувати<br>
                    сім’я зі свого сукупного доходу за місяць) менше розміру Вашого місячного платежу в<br>
                    межах норм, то Ви можете звернутися за житловою субсидією.<br>
                    </p>
                    <p>
                    Для визначення права на призначення житлово-комунальної субсидії Вам потрібно звер-<br>
                    нутись до спеціалістів управлінь праці та соціального захисту населення районних в місті<br>
                    Києві державних адміністрацій.<span class="q tooltip" title="257-23-87 - Голосіївський<br>563-96-42 - Дарницький<br>545-14-14 - Деснянський<br>542-66-09 - Дніпровський<br>467-98-58 - Оболонський<br>254-35-37 - Печерський<br>425-85-16 - Подільський<br>405-73-13 - Святошинський<br>207-39-12 - Солом’янський<br>238-00-51 - Шевченківський"></span>
                    </p>
                <? } elseif ($Err_BIG_DOHOD) { ?>
                    <h4>Перевірте, будь ласка, вхідні дані.</h3>
                    <br>
                    <p>
                    За наданою інформацією, Ваш сукупний дохід дозволяє сплачувати за житлово-комунальні послуги в повному обсязі, без державної дотації. Орієнтовний розрахунок відсотку обов'язкового платежу не проводиться.
                    </p>
                <? } else { ?>
                    <h4>Базові дані введено невірно!</h3>
                    <br>
                <? } ?>
                </div>
            </div>
            <?php
        }
    ?>

    <form action="<?= BASE_URL; ?>/calc-subsidies/" method="post">
        <div class="item-row">
            <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
            <div class="col-label">Середньомісячний сукупний дохід усіх<br>зареєстрованих членів сім’ї (грн) <span class="q tooltip" title="При розрахунку потрібно враховувати розмір нарахованого доходу<br>(а не фактично отриманого) за останні 6 місяців перед місяцем звертання<br>"></span></div>
            <div class="col-input">
                <input type="text" id="DOHOD" name="DOHOD" maxlength="10" class="txt num-short green bold s24 form-txt-input" value="<?=$DOHOD?>" onkeypress="return isNumberKey(event);" />
            </div>
        </div>
        <div class="item-row">
            <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
            <div class="col-label">Кількість осіб зареєстрованих<br>у житловому приміщенні <span class="q tooltip" title="На яких нараховуються комунальні послуги"></span></div>
            <div class="col-input">
                <input type="text" id="KL" name="KL" maxlength="1" onkeypress="return isNumberKey(event);" class="txt num-short green bold s24 form-txt-input" value="<?=$KL?>" />
            </div>
        </div>
        <button class="btn green bold">Розрахувати</button> 
    </form>
</div>
