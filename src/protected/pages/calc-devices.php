<h1>Розрахунок за показаннями квартирних<br>приладів обліку</h1>

<div class="calculator">
	<form action="#" method="post">
		<div class="calc-block">
			<div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-notepad.png" alt="" />Базовi данi</div>
			<div class="item-row">
				<div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-people.png" alt="" /></div>
				<div class="col-label">Кількість мешканців, які<br/>користуються послугами</div>
				<div class="col-input">
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="1" />
				</div>
			</div>
			<div class="item-row">
				<div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
				<div class="col-label">Кількість пільг, які надаються<br/>по даному особовому рахунку</div>
				<div class="col-input">
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="1" />
				</div>
			</div>
			<div class="calc-subblock">
				<div class="item-row">
					<div class="col-label">Кількість пільговиків, які користуються<br/>1-ю пільгою</div>
					<div class="col-input">
						<input type="text" class="txt num-short green bold s24 form-txt-input" value="1" />
					</div>
				</div>
				<div class="item-row">
					<div class="col-label">Кількість пільговиків, які користуються<br/>1-ю пільгою</div>
					<div class="col-input">
						<select class="dropdown">
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
				<div class="col-label">Норма споживання на одну особу</div>
				<div class="col-input">
						<select class="dropdown">
							<option>Оберiть м³</option>
							<option>2,4 м³</option>
							<option>4,0 м³</option>
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
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="1" />
				</div>
			</div>

			<div class="item-row">
				<div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
				<div class="col-label">Тариф за 1 м³ холодної води та<br/>водовідведення, грн</div>
				<div class="col-input">
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="10.24" />
				</div>
			</div>
		</div>
		<div class="calc-block">
			<div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-hot-water.png" alt="" />Гаряче водопостачання</div>
			<div class="item-row">
				<div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-abacus.png" alt="" /></div>
				<div class="col-label">Норма споживання на одну особу</div>
				<div class="col-input">
						<select class="dropdown">
							<option>Оберiть м³</option>
							<option>1,6 м³</option>
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
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="1" />
				</div>
			</div>

			<div class="item-row">
				<div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
				<div class="col-label">Тариф за 1 м³ гарячої води, грн</div>
				<div class="col-input">
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="40.92" />
				</div>
			</div>
		</div>
		<div class="calc-block">
			<div class="title"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-hot-water-out.png" alt="" />Водовідведення гарячої води</div>
			<div class="item-row">
				<div class="col-icon"><img src="<?= BASE_URL ?>/pic/pages/calculator/icon-money.png" alt="" /></div>
				<div class="col-label">Тариф за 1 м³ водовідведення гарячої води, грн</div>
				<div class="col-input">
					<input type="text" class="txt num-short green bold s24 form-txt-input" value="4.82" />
				</div>
			</div>
		</div>
		<button class="btn green bold">Розрахувати</button> 
	</form>
</div>