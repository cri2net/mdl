<h1>Розрахунок розміру обов’язкового платежу</h1>
<div class="calculator">
    <form action="#" method="POST">
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
        <button class="btn green bold">Розрахувати</button> 
    </form>
</div>
