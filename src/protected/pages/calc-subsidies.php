<h1>Розрахунок розміру обов’язкового платежу</h1>
<div class="calculator">
    <?php
        if (isset($_POST['action'])) {
            ?>
            <div class="result-block">
                <table class="no-border">
                    <tr>
                        <td class="title blue p50 rbw">Базовi данi</td>
                        <td class="title green p50 lbw">Розрахованi данi</td>
                    </tr>
                    <tr>
                        <td class="data rbw" >
                            <table class="no-border" >
                                <tr>
                                    <td>Сукупний дохід сім’ї</td>
                                    <td><b>5000 грн</b></td>
                                </tr>
                                <tr>
                                    <td>Кількість осіб</td>
                                    <td><b>1</b></td>
                                </tr>
                            </table>
                        </td>
                        <td class="data lbw" >
                            <table class="no-border" >
                                <tr>
                                    <td>Частка обов’язкової плати</td>
                                    <td><b class="green">31.89%</b></td>
                                </tr>
                                <tr>
                                    <td>Обов'язкова плата за ЖКП</td>
                                    <td><b class="green">1594.5 грн</b></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="inner alert" >
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
                </div>
            </div>
            <?php
        }
    ?>

    <form action="<?= BASE_URL; ?>/calc-subsidies/" method="post">
        <div class="item-row">
            <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
            <div class="col-label">Середньомісячний сукупний дохід усіх<br>зареєстрованих членів сім’ї (грн) <span class="q tooltip" title="При розрахунку потрібно враховувати розмір нарахованого доходу<br>(а не фактично отриманого) за останні 6 місяців перед місяцем звертання<br>" ></span></div>
            <div class="col-input">
                <input type="text" name="DOHOD" maxlength="10" class="txt num-short green bold s24 form-txt-input" />
            </div>
        </div>
        <div class="item-row">
            <div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
            <div class="col-label">Кількість осіб зареєстрованих<br>у житловому приміщенні <span class="q tooltip" title="На яких нараховуються комунальні послуги"></span></div>
            <div class="col-input">
                <input type="text" id="KL" name="KL" maxlength="1" onkeypress="return isNumberKey(event);" class="txt num-short green bold s24 form-txt-input" />
            </div>
        </div>
        <input type="hidden" name="action" value="calculate" />
        <button class="btn green bold">Розрахувати</button> 
    </form>
</div>
