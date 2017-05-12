<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?= $totalAmountKop; ?>">
<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?= base64_encode("Сплата комунальних послуг"); ?>">
<input type="hidden" name="LMI_PAYMENT_NO" value="<?= $_payment['id']; ?>">
<input type="hidden" name="LMI_PAYEE_PURSE" value="<?= LMI_PAYEE_PURSE; ?>">
<input type="hidden" name="LMI_SUCCESS_URL" value="<?= BASE_URL; ?>/payment-status/<?= $_payment['id']; ?>/">
<input type="hidden" name="LMI_FAIL_URL" value="<?= BASE_URL; ?>/payment-status/<?= $_payment['id']; ?>/">
