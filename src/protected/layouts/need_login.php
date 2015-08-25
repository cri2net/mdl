<?php
	$have_error_login = (isset($_POST['login']) && $_POST['login']);
?>
<script type="text/javascript">
	$('title').html('ГЕРЦ — Необходима авторизация');
	$('#content').addClass('infocenter');
</script>
<div class="mini_info_block">
	<h2 class="news">Для доступа к странице необходимо авторизоваться</h2>
</div>
<div style="" class="attention">
	<br>
	<p>Просмотр этой страницы доступен только для зарегистрированных пользователей</p>
	<div style="<?= $have_error_login ? '' : 'display:none'; ?>" class="massage massage-attention">
		<div class="inner">
			<div class="content">
				<p style="color:#900;">Неправильно введен электронный адрес или пароль</p>
			</div>
		</div>
	</div>
	<br>
</div>
<div class="oplata-box">
	<div class="dom-box" style="padding-top:5px; height:145px;" onclick="location.href='<?= BASE_URL; ?>/registration/';">
		<h4>Регистрация</h4>
		<p style="font-size:12px;">Нажмите здесь, чтобы зарегистрироваться в системе.</p>
		<p style="font-size:12px;">Это займет у Вас меньше минуты.</p>
		<div class="blue_button">
			<input type="button" value="Регистрация" />
		</div>
	</div>
	<div class="newlogin-box" style="padding-top:5px; padding-left:10px; width:335px; height:145px;">
		<form name="register-form" action="" method="post" class="login-form">
			<h4>Войти</h4>
			<div class="input" style="margin-bottom:4px;">
				<input style="margin-left:100px;" class="text" title="Ваш e-mail" type="email" placeholder="email@mailto.com" value="<?= htmlspecialchars(stripslashes(isset($_POST['login_email']) ? $_POST['login_email'] : ''), ENT_QUOTES); ?>" autofocus="autofocus" name="email_login"/>
			</div>
			<div class="input" style="margin-bottom:4px;">
				<input style="margin-left:100px;" id="password" title="Введите пароль" class="text" type="password" placeholder="Пароль" name="password"/>
			</div>
			<div style="margin-top:5px; position:relative; text-align:center; margin-bottom:-13px; height:17px;" class="remember-me">
				<input type="checkbox" name="remember-me" id="remember-me" checked="checked" style="margin-right:8px; position:relative; top:2px; width:10px;"><label for="remember-me">Запомнить меня</label>
			</div>
			<div class="blue_button" style="text-align:center;">
				<input type="submit" style="margin-top:18px;" value="Войти" />
				<a style="<?= ($have_error_login) ? '' : 'display:none'; ?>" href="<?= BASE_URL; ?>/restore/" class="rempas">Забыли пароль?</a>
				<input type="hidden" value="1" name="login"/>
			</div>
		</form>
	</div>
	<div class="clearr"></div>
</div>