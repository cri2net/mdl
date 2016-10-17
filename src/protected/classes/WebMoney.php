<?php
class WebMoney
{
    public static function checkSignature($data)
    {
        $str = $data['LMI_PAYEE_PURSE'].$data['LMI_PAYMENT_AMOUNT'].$data['LMI_PAYMENT_NO'].$data['LMI_MODE'].
               $data['LMI_SYS_INVS_NO'].$data['LMI_SYS_TRANS_NO'].$data['LMI_SYS_TRANS_DATE'].
               $data['LMI_SECRET_KEY'].$data['LMI_PAYER_PURSE'].$data['LMI_PAYER_WM'];
        $hash = strtoupper(md5($str));
        if ($hash == $data['LMI_HASH']){
            return true;
        }
        throw new Exception('Incorrect signature.');
    }
}
