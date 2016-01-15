<?php
$payments = PDO_DB::table_list(
    ShoppingCart::TABLE,
    "user_id={$__userData['id']} AND processing IS NOT NULL",
    "go_to_payment_time DESC"
);

if ($_SERVER['REMOTE_ADDR'] == '193.200.205.133' || $_SERVER['REMOTE_ADDR'] == '172.16.64.8' || $__userData['id'] == 20 || $__userData['id'] == 31){
    //Отладка нового метода
    include_once ROOT .'/protected/classes/xifaceClass.php';
    
    $flats = Flat::getUserFlats($__userData['id']);
    $userFlatIDs = array();
    foreach ($flats as $flat){
    
        $pin = Flat::getFlatPINByID($flat['flat_id']);
        //$userFlatIDs[]['flat_id'] = $flat['flat_id'];
        $userFlatIDs[] = $pin;
    }
    //var_dump($userFlatIDs);
    //Flat::getFlatPINByID($flatID);
    $payments = array();
    
    foreach ($userFlatIDs as $key => $pin){
        $pacc = (integer)substr($pin,-6);
        $jek = ($pin-$pacc)/1000000;
        
        
        $xIfaceClass = new xIfaceClass();
        $xIfaceClass->Query(
            'Municipal-2.0', 'GetPayments', 
            array('QueryParams'=>array('@JEK'=>$jek, '@PACC'=>$pacc, /*'@LimitLast' => 20*/)),
            strtotime(date('Y-m-d 06:00:00',strtotime('+1 day')))-time() // timeout tomorrow morning
        );
        //var_dump($xIfaceClass->replyXML);
        $resXMLObj = new SimpleXMLElement($xIfaceClass->replyXML->saveXML());
        // echo $xIfaceClass->result;
        // echo $xIfaceClass->resultCode;
        
        //var_dump($resXMLObj->Data->Payments->Payment[1]);
        foreach( $resXMLObj->Data->Payments->Payment as $pay ){
            //var_dump($pay);
            
            //echo (string)$pay->PayerInfo->StreetAddress;
            print_r($pay->PayerInfo->textContent);
            
            //hexdec("00024d85")
            $payment['id'] = hexdec(substr(((string)$pay->attributes()->RSBPaymentID),0,8));
            $payment['go_to_payment_time'] = strtotime ((string)$pay->PayDate);
            //$payment['type'] ="komdebt"; //Комунальні послуги
            $payment['summ_total'] = (string)$pay->Summ;
            $payment['title'] = (string)$pay->Bill->Title." ";
            $payStatus = "new";
            switch ((string)$pay->Status){
                case "Enrolled":
                    $payStatus = "success";
                    break;
            }
            $payment['status'] = $payStatus;
            
            $payments[] = $payment;
        }
    }
}

require_once(ROOT . '/protected/scripts/cabinet/payments/_list.php');
