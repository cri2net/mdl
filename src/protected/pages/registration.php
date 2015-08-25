<?php
	$months = array('1'=>'январь', '2'=>'февраль', '3'=>'март', '4'=>'апрель', '5'=>'май', '6'=>'июнь', '7'=>'июль', '8'=>'август', '9'=>'сентябрь', '10'=>'октябрь', '11'=>'ноябрь', '12'=>'декабрь');
	
	$years = array();
	$days = array();
	
	for($i=1900; $i<=date("Y"); $i++)
		$years[] = $i;

	for($i=1; $i<=31; $i++)
	{
		if ($i < 10)
			$days[] = "0".$i;
		else
			$days[] = $i;
	}
	
	if (!empty($_POST['register']) && $_POST['register'] == 1)
	{
		try
		{
			$_POST['spam_invoice'] = (isset($_POST['spam_invoice']))?'1':'0';
			$_POST['spam_mail'] = (isset($_POST['spam_mail']))?'1':'0';
			RegistrationProcess::registerUser($this->smarty, $_POST); 
			unset($_SESSION['security_code']);
			$_SESSION['fname'] = $_POST['first_name'];
			$_SESSION['mname'] = $_POST['middle_name']; 
			$_SESSION['email'] = $_POST['email'];

			// slav 12.08.2013 AutoLogIn after registration
			Authorization::login($_POST['email'], $_POST['password']);
			
			$mail = new Mail($this->smarty, "after_registration.tpl", array(
				'fname'=>$_POST['first_name'],
				'mname'=>$_POST['middle_name'],
				'email'=>$_POST['email'],
				'pass'=>$_POST['password']
			));
			$mail->send($_POST['email'], 'Регистрация');

			header("Location: https://{$_SERVER['HTTP_HOST']}/infocenter/");
			exit();
		}
		catch(Exception $e)
		{
			$this->smarty->assign('error', '');
			$this->smarty->assign('error_msg', $e->getMessage());
			if(strcmp($e->getMessage(), THIS_EMAIL_ALREADY_EXISTS) == 0)
				$this->smarty->assign('error_msg', $e->getMessage().'<br><a style="color:#fff;" href="https://www.gerc.ua/restore/">Забыли пароль?</a>');
		}
	}


	if(Authorization::isLogin())
	{
		?>
		<div class="mini_info_block">
			<h2 class="reg">Вы уже зарегестрированы</h2>
		</div>
		<?php
		return;
	}
?>
<div class="mini_info_block">
	<h2 class="reg">Регистрация пользователя</h2>
</div>
<?php
	if($error)
	{
		?>
		<div class="pays-wrapper">
			<div class="pays">
				<div class="pays-table" style="width:520px;">
					<div id="error"><?= $error; ?></div>
				</div>
			</div>
		</div>
		<?php
		return;
	}
?>

<form class="gerts-register" name="gerts-register" method="post" action="" id="register">
	<div class="error" style="{*$error*}" id="error_reg">
		<p><span id="error_msg">{*$error_msg*}</span><br /></p>
	</div>
	<div class="input">
		<label for="reg-surname">Фамилия:</label>
		<input id="last_name" type="text" name="last_name" value="{*$smarty.post.last_name*}"/>
	</div>
	<div class="input">
		<label for="reg-name">Имя:</label>
		<input id="first_name" type="text" name="first_name" value="{*$smarty.post.first_name*}"/>
	</div>
	<div class="input">
		<label for="reg-patronymic">Отчество:</label>
		<input id="middle_name" type="text" name="middle_name" value="{*$smarty.post.middle_name*}"/>
	</div>
	<!-- Убрала ввод даты рождения -->
	<!--<div class="select">
		<label>Дата рождения:</label>
		<select id="day" name="day" style="width:55px;">
			{*foreach from=$days item=day*}
				<option {*if $day==$smarty.post.day*} selected{*/if*}>{*$day*}</option>
			{*/foreach*}
		</select>
		<select id="month" name="month" class="mes" style="width:100px;">
			{*foreach from=$months key=key item=month*}
				<option value="{*$key*}" {*if $smarty.post.month == $key*} selected{*/if*}>{*$month*}</option>
			{*/foreach*}
		</select>
		<select id="year" name="year" style="width:75px;">
			{*foreach from=$years item=years*}
				<option {*if $smarty.post.year == $years*} selected {*elseif $years==1970*} selected{*/if*}>{*$years*}</option>
			{*/foreach*}
		</select>
	</div>-->
	<div class="clearr"></div>
	<div class="input">
		<label for="reg-E-Mail">Электронный адрес:</label>
		<input id="email" type="email" name="email" value="{*$smarty.post.email*}"/>
	</div>
	<div class="input">
		<label for="reg-E-Mail">Телефон:</label>
		<input id="phone_num" type="text" name="phone_num" value="{*$smarty.post.phone_num*}"/>
		<p style="padding-left: 212px"><br>Формат (048)788-98-00 или (067)333-22-11</p>
	</div>
	<div class="input pass">
		<label for="reg-password">Пароль:</label>
		<input id="password" type="password" name="password"/>
	</div>
	<div class="input pass">
		<label for="reg-p">Повторить пароль:</label>
		<input id="repassword" type="password" name="repassword"/>&nbsp;&nbsp;Пароль должен быть не менее 6 символов.
	</div>
	<div class="input">
		<input type="checkbox" style="margin-left:214px; margin-right:15px; width:10px;" name="spam_mail" id="spam_mail" checked="checked" />
		<label style="float:none;" for="spam_mail">Я хочу получать новости портала</label>
	</div>
	<div class="input">
		<input type="checkbox" style="margin-left:214px; margin-right:15px; width:10px;" name="spam_invoice" id="spam_invoice" checked="checked" />
		<label style="float:none;" for="spam_invoice">Я хочу получать счета-уведомления (1 раз в месяц)</label>
	</div>
	<div class="input">
		<input id="policy" type="checkbox" name="policy" value="1" checked="checked"/>
		<label style="float:none;" for="policy">Я согласен с условиями <a href="./policy/" target="_blank">Пользовательского соглашения</a></label>
	</div><div class="clearr"></div><div class="clear"></div>
	<div class="blue_button registration">
		<input type="submit" value="ЗАРЕГИСТРИРОВАТЬСЯ" />
	</div>
	<input type="hidden" value="1" name="register"/>
</form><div class="clearr"></div>

<script type="text/javascript" src="./js/jquery.maskedinput-1.2.2.min.js"></script>
<script type="text/javascript">
jQuery(function($){
   $("#phone_num").mask("(999)999-99-99");
});
</script>