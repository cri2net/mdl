<?php
	if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

	try {
		$pay_system = $_POST['cctype'];
		$user_id = Authorization::getLoggedUserId();
		if (!in_array($pay_system, ShoppingCart::getActivePaySystems())){
			throw new Exception("UNKNOW PAY SYSTEM $pay_system");
		}

		$_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_SESSION['payment_id']);
		if ($_payment == null) {
			throw new Exception("UNKNOW PAYMENT ID {$_SESSION['payment_id']}");
		}

		$_debp_sum = $_payment['summ_plat'];
		$percent = ShoppingCart::getPercent($_debp_sum);
		$percent = $percent[$pay_system]['percent'];
		$payment_id = $_payment['id'];


		$commissionSum = ShoppingCart::getPercentSum($_debp_sum, $pay_system);
		$file = ROOT . "/protected/conf/payments/$pay_system/$pay_system";
		if (file_exists($file . ".conf.php")) {
			require_once($file . ".conf.php");
		}

		$totalAmount = $_debp_sum + $commissionSum;
		
		$cdata = array(
			'processing' => $pay_system,
			'summ_komis' => $commissionSum,
			'summ_total' => $totalAmount,
			'persent' => $percent,
			'go_to_payment_time' => microtime(true),
		);
		PDO_DB::updateWithWhere($cdata, ShoppingCart::TABLE, "id='{$_payment['id']}' AND user_id='$user_id'");

		$purchaseTime = strftime("%y%m%d%H%M%S");
		
		$sd = session_id();
		$totalAmountKop = $totalAmount * 100;
		
		if (file_exists($file . ".process.php")) {
			require_once($file . ".process.php");
		}
		
		$debt_sum = str_replace(".", ",", $_debp_sum);
		$commissionSum = str_replace(".", ",", $commissionSum);
		$totalAmount = str_replace(".", ",", $totalAmount);
		unset($_SESSION['payment_id']);

		ShoppingCart::send_payment_to_reports($_payment['id']);
	} catch(Exception $e) {
		$error = $e->getMessage();
	}
?>
<div class="mini_info_block">
	<h2 class="news">Электронная касса</h2>
</div>
<?php
	if($error)
	{
		?><div class="oplata-box"><div id="error"><?= $error; ?></div></div> <?php
		return;
	}
?>
<p class="p-ch">Подтвердите  правильность введенных данных.</p>
<div class="pays-table2">
	<form target="<?= $payment_form_target; ?>" action="<?= $payment_form_action; ?>" method="post" name="f" id="f">
		<table width="100%" border="0" class="pays-table-summ" style="border-collapse: collapse; font-size: 11pt">
			<tr>
				<td class="frst" nowrap="nowrap">&nbsp;</td>
				<td width="74%">Оплата коммунальных услуг (Код: <?= $payment_id; ?>)</td>
				<td width="15%" nowrap="nowrap">&nbsp;</td>
				<td width="15%" nowrap="nowrap" style="text-align: right;"><?= $debt_sum; ?> грн</td>
			</tr>
			<tr>
				<td class="frst" nowrap="nowrap">&nbsp;</td>
				<td width="74%">Услуги портала </td>
				<td width="15%" nowrap="nowrap">&nbsp;</td>
				<td width="15%" nowrap="nowrap" style="text-align: right;"><?= $commissionSum; ?> грн</td>
			</tr>
			<tr>
				<td class="frst" nowrap="nowrap">&nbsp;</td>
				<td width="74%">Итого  : </td>
				<td width="15%" nowrap="nowrap">&nbsp;</td>
				<td width="15%" nowrap="nowrap" style="text-align: right;"><?= $totalAmount; ?> грн</td>
			</tr>
			<tr>
				<td class="frst" nowrap="nowrap">&nbsp;</td>
				<td width="74%" nowrap="nowrap">&nbsp;</td>
				<td width="15%" nowrap="nowrap">&nbsp;</td>
				<td width="15%" nowrap="nowrap">
					<input type="submit" value="Оплатить">
				</td>
			</tr>
		</table>
		<?php
			if(file_exists($file . ".payform.php"))
				require_once($file . ".payform.php");
		?>
	</form>
</div>
<?php
	if ($pay_system == 'imeks') {
		?>
		<input type="hidden" name="cart_status" id="cart_status" size="2" value="0">
		<input type="hidden" id="O_ID" value="<?= $payment_id; ?>">

		<div id="waiting" class="visible">
		</div>
		<div id="check_status" class="invisible">
		<br>
		<form action="<?= $payment_form_action; ?>" method="post" name="payment" id="payment">
			<input type="hidden" id="OrderID" name="OrderID" value="<?= $payment_id; ?>">
			<input type="hidden" name="AfterPayment" value="1">
			<center><input type="button" class="button_orange" value="   Далее >>   " onclick="imeksPaymentNext();"></center>
		</form>
		</div>
		<?php
	} elseif ($pay_system == 'imeks') {
		?>
		<script type="text/javascript">
			function MarfinPayment()
			{
				var cart_id = $('#payment #OrderID').val();

				var tData = {};
				tData.obj = 'MarfinProcess';
				tData.ac = 'MarfinPayment';
				tData.cart_id = cart_id;

				jQuery.ajax({
					dataType: 'json',
					data: tData,
					type: 'POST',
					url : './service/',

					success : function(data, textStatus){
						var res = eval(data);
						if (res.success == true){
							if(res.record.status == 1) {
								document.getElementById('marfin_notice').innerHTML = 'ОПЛАТА УСПЕШНО ЗАВЕРШЕНА!';
								return false;
							}
							if(res.record.status == 2) {
								document.getElementById('marfin_notice').innerHTML = 'ТРАНЗАКЦИЯ ЗАКРЫТА ПО ТАЙМАУТУ (1 СУТКИ)';
								return false;
							}
							setTimeout(MarfinPayment, 10000);
						}
						else
							alert(res.record.msg);
					}
				});
			};
			function win_open()
			{
				var action = document.forms[0].action.value;
				MarfinPayment();
				window.open(action, '', 'width=700, height=500, left=400, top=0, resizable=1, scrollbars=1, menubar=1');
				document.getElementById('marfin_submit').innerHTML = "";
			}
		</script>
		<div class="pays-table2" style='font-size:14pt' id="marfin_notice">
			В системе Интернет-Банкинг  ПАО «МАРФИН БАНК» в форме платежа ГЕРЦ введите сумму
			<font color="red"><u><?= $totalAmountKop / 100; ?> грн.</u></font> и код платежа <font color="red"><u><?= $payment_id; ?></u></font>
		</div>
		<?php
	}
?>