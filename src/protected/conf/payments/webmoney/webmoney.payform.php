<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?= $totalAmountWM; ?>">  
<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?= $wm_description; ?>">  
<input type="hidden" name="LMI_PAYMENT_NO" value="<?= $payment_id; ?>">  
<input type="hidden" name="LMI_PAYEE_PURSE" value="<?= LMI_PAYEE_PURSE; ?>">  
<!-- input type="hidden" name="LMI_SIM_MODE" value="2"--><!-- for test only -->
<input type="hidden" name="LMI_SUCCESS_URL" value="<?= BASE_URL; ?>/successpay/<?= $payment_id; ?>">
<input type="hidden" name="LMI_FAIL_URL" value="<?= BASE_URL; ?>/errorpay/<?= $payment_id; ?>">