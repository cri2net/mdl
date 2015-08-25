<input type="hidden" name="Version" value="1">
<input type="hidden" name="MerchantID" value="<?= $MerchantID; ?>">
<input type="hidden" name="TerminalID" value="<?= $TerminalID; ?>">
<input type="hidden" name="TotalAmount" id="TotalAmount" value="<?= $totalAmountKop; ?>">
<input type="hidden" name="Currency" value="980">
<input type="hidden" name="locale" value="ru">
<input type="hidden" name="SD" value="<?= $sd; ?>">
<input type="hidden" name="OrderID" value="<?= $_payment['id']; ?>">
<input type="hidden" name="PurchaseTime" value="<?= $purchaseTime; ?>">
<input type="hidden" name="PurchaseDesc" value="Оплата коммунальных услуг">
<input type="hidden" name="Signature" value="<?= $signature; ?>">