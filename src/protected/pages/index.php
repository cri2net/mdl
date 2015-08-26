<div class="home">



	<slider>
		<div class="slider-btn next-btn" onclick="next_slide_rotate_index();"></div>
		<div class="slider-btn prev-btn" onclick="prev_slide_rotate_index();"></div>
		<a id="slide_0" href="#" style="display:block">
			<img src="<?= BASE_URL; ?>/db_pic/slide_1.jpg" alt="" />
		</a>
		<a id="slide_1" href="#" style="display:none">
			<img src="<?= BASE_URL; ?>/db_pic/slide_1.jpg" alt="" />
		</a>
		<a id="slide_2" href="#" style="display:none">
			<img src="<?= BASE_URL; ?>/db_pic/slide_1.jpg" alt="" />
		</a>
			
		<div class="bullets">
			<div class="bullet active" id="bullet_0" onclick="jump_to_slide(0);"></div>
			<div class="bullet" id="bullet_1" onclick="jump_to_slide(1);"></div>
			<div class="bullet" id="bullet_2" onclick="jump_to_slide(2);"></div>
		</div>

		<script type="text/javascript">
			var slide_count = 3;

			$('.bullets').everyTime(4000, 'bullets_svg', function() {
				next_slide_rotate_index();
			});
		</script>
	</slider>






	<div class="features">
		<div class="feature left">
			<div class="title">Сплачуйте за послуги ЖКГ он-лайн</div>
			<ul>
				<li class="visa">Зручним способом
				<li class="cabinet">Контролюй сплати у особистому кабінеті
				<li class="account">Всі об'єкти під одним аккаунтом
				<li class="email">Отримуй email-повідомлення про оновлення рахунків
			</ul>
		</div>
		<div class="feature right">
			<div class="title">Де сплатити послуги ЖКГ?</div>
			<ul>
				<li class="bank">Банки
				<li class="terminal">Термiнали самооблуговування
			</ul>
		</div>
	</div>

	<h1 class="big-title">Останнi новини</h1>
	<div class="news-list">
		<div class="news-item first">
			<h2 class="title">Про внесення змiн до норм споживання</h2>
			<div class="date">15 травня</div>
			<div class="announce">
				Кабінет Міністрів України постановив про внесення змін до норм споживання природного газу населенням у разі відсутності газових лічильників. Постанова КМУ від 29.04.2015 № 237
			</div>
			<div class="details"><a href="#">детальнiше...</a></div>
		</div>
		<div class="news-item">
			<h2 class="title">Куди звертатися для отримання субсидій?</h2>
			<div class="date">12 квiтня</div>
			<div class="announce">
				Процедура отримання субсидій здійснюється в районних управліннях праці та соціального захисту. На дошках оголошень під'їздах житлових будинків розміщено про адреси управлінь та їх контактні телефони.
			</div>
			<div class="details"><a href="#">детальнiше...</a></div>
		</div>
	</div>
	<h1 class="big-title green">Останнi матерiали для споживачiв</h1>
	<div class="news-list">
		<div class="news-item first">
			<h2 class="title">Про внесення змiн до норм споживання</h2>
			<div class="date">15 травня</div>
			<div class="announce">
				Кабінет Міністрів України постановив про внесення змін до норм споживання природного газу населенням у разі відсутності газових лічильників. Постанова КМУ від 29.04.2015 № 237
			</div>
			<div class="details"><a href="#">детальнiше...</a></div>
		</div>
		<div class="news-item">
			<h2 class="title">Куди звертатися для отримання субсидій?</h2>
			<div class="date">12 квiтня</div>
			<div class="announce">
				Процедура отримання субсидій здійснюється в районних управліннях праці та соціального захисту. На дошках оголошень під'їздах житлових будинків розміщено про адреси управлінь та їх контактні телефони.
			</div>
			<div class="details"><a href="#">детальнiше...</a></div>
		</div>
	</div>
</div>