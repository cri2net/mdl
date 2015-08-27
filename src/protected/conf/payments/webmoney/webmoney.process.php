<?php
    // require_once(WM_CONF."wm.conf.php");
    // $this->smarty->assign('LMI_PAYEE_PURSE', LMI_PAYEE_PURSE);
    $totalAmountWM = $totalAmount;
    $wm_description = base64_encode("Оплата коммунальных услуг. " . BASE_URL);
