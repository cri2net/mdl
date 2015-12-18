<?php

class Khreshchatyk
{
    protected $SVFE_host;
    protected $SVFE_port;
    protected $Terminal_ID;
    protected $Merchant_ID;

    public function __construct()
    {
        $this->SVFE_host = '10.192.1.241';
        $this->SVFE_port = 12406;

        // написано, что будет по мерчанту для каждого поставщика услуг
        
        // test terminal & merchant 1
        $this->Terminal_ID = 'XETEST01';
        $this->Merchant_ID = 'XETEST000000001';

        // test terminal & merchant 2
        // $this->Terminal_ID = 'XETEST02';
        // $this->Merchant_ID = 'XETEST000000002';
    }

    private function sendISO($jak_obj)
    {
        $iso = $jak_obj->getMTI() . hex2bin($jak_obj->getBitmap()) . implode($jak_obj->getData());

        var_dump($iso);
        var_dump(date('d.m.Y H:i:s'));
        
        $fp = fsockopen($this->SVFE_host, $this->SVFE_port, $errno, $errstr, 10);
        if (!$fp) {
            echo "$errstr ($errno)<br />\r\n";
        } else {
            fwrite($fp, $iso);
            echo "\r\n<br>\r\n";

            $time = microtime(true);
            $a = '';
            while (microtime(true) - $time < 15) {
                $a .= fgets($fp, 10240);
            }
            
            echo bin2hex($a);
            echo "<br>$a";
            echo "<br>".strlen($a);
            // echo fgets($fp, 1024);
            // echo fgets($fp, 1024);
            fclose($fp);
            die();
        }

        die('----die');

        return $response;
    }

    public function checkBalance()
    {
        $jak = new JAK8583();
        $date = date('mdHis');

        $jak->addMTI('0200');
        $local_date = date('ymdHis');
        $local_date = '151217161955';

        $jak->addData(3,   '000000');
        $jak->addData(4,   '000000001600');
        $jak->addData(7,   $date);
        $jak->addData(11,  '000001');
        $jak->addData(12,  $local_date);
        $jak->addData(18,  '4900');
        $jak->addData(41,  $this->Terminal_ID);
        $jak->addData(42,  $this->Merchant_ID);
        $jak->addData(49,  '980');
        $jak->addData(102, '1110922885201'); // 11 - длина, потом номер 10922885201

        // $iso = $jak->getISO();



        // $jak->addData(11, 286808);
        // $jak->addData(70, '301');
        
        // return $jak->getISO();
        

        // $message = $jak->getMTI();
        // $message .= bin2hex($jak->getBitmap());
        // $hex_bimap = '';

        // var_dump($bitmap));
        // die();

        // $message .= $jak->getBitmap();

        // var_dump($jak->getISO());
        // var_dump($message);
        // die(__FILE__ . ":" . __LINE__);
        return $this->sendISO($jak);
    }
}
