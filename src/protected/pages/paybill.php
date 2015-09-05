<?php
	if (!Authorization::isLogin()) {
        define('SHOW_NEED_AUTH_MESSAGE', true);
        return require_once(ROOT . '/protected/pages/cabinet/login.php');
    }

	$nextButton = (isset($_SESSION['bill']) && $_SESSION['bill'] == 1) ? '' : 'display: none;';
	
	if (!empty($_POST['pay']) && $_POST['pay'] == 1 && (isset($_SESSION['bill']) && $_SESSION['bill'] == 1))
	{
		unset($_SESSION['bill']);
		
		try
		{
			$total_sum = ShoppingCart::getTotalDebtSum($_POST);
			if($total_sum == 0)
				throw new Exception(ERROR_INVALID_ZERO_PAYMENT);

			$percent = ShoppingCart::getPercent($total_sum);
			$pay_systems = ShoppingCart::getActivePaySystems();
			
			for ($i=0; $i < count($pay_systems); $i++)
			{
				$var = $pay_systems[$i] . 'Sum';
				$$var = str_replace(".", ",", ShoppingCart::getPercentSum($total_sum, $pay_systems[$i]));
			}

			$totalBillSum = $total_sum + ShoppingCart::getPercentSum($total_sum, $pay_systems[0]);
			$totalBillSum = sprintf('%.2f', $totalBillSum);

			$payment_id = ShoppingCart::add($_POST, Authorization::getLoggedUserId());
			
			$_SESSION['payment_id'] = $payment_id;
			$totalBillSum = str_replace(".", ",", $totalBillSum);
			$total_sum = str_replace(".", ",", $total_sum);
		}
		catch(Exception $e)
		{
			$error = $e->getMessage();
		}
	}
	else
		$error = 'Ваш сеанс устарел, пожалуйста, повторите ваш запрос';

?>
<div class="mini_info_block">
	<h2 class="news">Электронная касса</h2>
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
<div class="pays-wrapper">
	<div class="pays">
		<p class="p-ch">Выберите, пожалуйста, метод оплаты:</p>
		<div class="pays-table" style="width:520px;">
			<form action="<?= BASE_URL; ?>/checkout/" method="post" name="next_step">
				<table width="100%" border="0" class="pays-table-summ" style="border-collapse: collapse;">
					<tr>
						<td class="frst" nowrap="nowrap">&nbsp;</td>
						<td width="74%">Оплата коммунальных услуг (<b>Код: <?= $payment_id; ?></b>)</td>
						<td width="15%" nowrap="nowrap">&nbsp;</td>
						<td width="15%" nowrap="nowrap" style="text-align: right;"><?= $total_sum; ?> грн</td>
					</tr>
					<tr>
						<td class="frst" width="35" nowrap="nowrap">&nbsp;</td>
						<td>Услуги портала</td>
						<td width="15%" nowrap="nowrap">&nbsp;</td>
						<td width="15%" nowrap="nowrap" style="text-align: right;"><div id="comission_sum"></div></td>
					</tr>
					<?php
						if(in_array('visa', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" width="35" valign="middle" nowrap="nowrap"><input name="percent" type="radio" id="radio" value="<?= $percent['visa']['percent']; ?>" checked="checked" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $visaSum; ?>', 'visa');"/></td>
								<td valign="top">
									<label for="radio">
										<img style="display:block; float:left; margin-right:4px;" alt="visa" src="<?= BASE_URL; ?>/images/paysystems/visa-logo.png" />
										<span style="display:inline-block; margin-top:10px;">Карта Visa, Visa Electron</span>
									</label>
								</td>
							</tr>
							<?php
						}
						
						if(in_array('mastercard', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio2" value="<?= $percent['mastercard']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $mastercardSum; ?>', 'mastercard');"/></td>
								<td><label for="radio2"><img style="display:block; float:left; margin-right:4px;" alt="mastercard" src="<?= BASE_URL; ?>/images/paysystems/mastercard-logo.png" /><span style="display:inline-block; margin-top:10px;">Карта MasterCard, Maestro</span></label></td>
							</tr>
							<?php
						}

						if(in_array('_test_upc', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio_test_upc" value="<?= $percent['_test_upc']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $mastercardSum; ?>', '_test_upc');"/></td>
								<td><label for="radio_test_upc"><img style="display:block; float:left; margin-right:4px;" alt="" src="<?= BASE_URL; ?>/images/paysystems/visa-logo.png" /><span style="color:#f00; display:inline-block; margin-top:10px;"><b>Тестовый мерчант UPC</b></span></label></td>
							</tr>
							<?php
						}

						if(in_array('private', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio3" value="<?= $percent['private']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $privateSum; ?>', 'private');"/></td>
								<td><label for="radio3"><img style="display:block; float:left; margin-right:4px;" alt="privatbank" src="<?= BASE_URL; ?>/images/paysystems/pb-logo.png" /><span style="display:inline-block; margin-top:10px;">Карта банка "Приватбанк"</span></label></td>
							</tr>
							<?php
						}
					
						if(in_array('imeks', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio4" value="<?= $percent['imeks']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $imeksSum; ?>', 'imeks');"/></td>
								<td><label for="radio4"><img style="display:block; float:left; margin-right:4px;" alt="НСМЭП" src="<?= BASE_URL; ?>/images/paysystems/nsmep-logo.png" /><span style="display:inline-block; margin-top:10px;">Карта НСМЭП</span></label></td>
							</tr>
							<?php
						}

						if(in_array('mtb', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio6" value="<?= $percent['mtb']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $mtbSum; ?>', 'mtb');"/></td>
								<td style="min-width:310px;"><label for="radio6"><img style="display:block; float:left; margin-right:4px;" alt="marfin bank" src="<?= BASE_URL; ?>/images/paysystems/marfin.png" /><span style="display:inline-block; margin-top:18px; margin-left:7px;">Интернет-Банкинг&nbsp;ПАО&nbsp;«МАРФИН&nbsp;БАНК»</span></label></td>
							</tr>
							<?php
						}

						
						if(in_array('webmoney', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio5" value="<?= $percent['webmoney']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $webmoneySum; ?>', 'webmoney');"/></td>
								<td><label for="radio5"><img style="display:block; float:left; margin-right:4px;" alt="Webmoney" src="<?= BASE_URL; ?>/images/paysystems/wm-logo.png" /><span style="display:inline-block; margin-top:10px;">Webmoney</span></label></td>
							</tr>
							<?php
						}
						
						if(in_array('w1', $pay_systems))
						{
							?>
							<tr>
								<td class="frst" nowrap="nowrap" valign="middle"><input type="radio" name="percent" id="radio7" value="<?= $percent['w1']['percent']; ?>" onclick="getShoppingCartTotal('<?= $total_sum; ?>', '<?= $w1Sum; ?>', 'w1');"/></td>
								<td><label for="radio7"><img style="display:block; float:left; margin-right:4px;" alt="Единый кошелек" src="<?= BASE_URL; ?>/images/paysystems/ek.png" /><span style="display:inline-block; margin-top:10px;">Единый кошелек</span></label></td>
							</tr>
							<?php
						}
					?>
					<tr>
						<td class="frst" nowrap="nowrap">&nbsp;</td>
						<td colspan="2" align="right" class="bigfont">Итого к оплате:</td>
						<td class="bigfont" nowrap="nowrap" id="totalBillSum"><?= $totalBillSum; ?> грн</td>
					</tr>
					<tr>
						<td colspan="4" align="right" style="<?= $nextButton; ?>">
							<input type="submit" value="Продолжить" />
							<input type="hidden" name="cctype" id="cctype" value="<?= $pay_systems[0]; ?>">
							<input type="hidden" value="1" name="checkout_submited">
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	getShoppingCartTotal('<?= $total_sum; ?>', '<?= $visaSum; ?>', '1');
</script>
