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
        $this->Terminal_ID = XETEST01;
        $this->Merchant_ID = XETEST000000001;

        // test terminal & merchant 2
        // $this->Terminal_ID = XETEST02;
        // $this->Merchant_ID = XETEST000000002;
    }

    private function sendISO($iso)
    {
        $fp = fsockopen($this->SVFE_host, $this->SVFE_port, $errno, $errstr, 10);
        if (!$fp) {
            echo "$errstr ($errno)<br />\r\n";
        } else {
            fwrite($fp, $iso);
            fclose($fp);
            var_dump($fp);
            die();
        
            echo fgets($fp, 128);
            die('d7');
        
            while (!feof($fp)) {
                echo fgets($fp, 128);
            }
            fclose($fp);
        }

        die('----die');

        return $response;
    }

    public function checkBalance()
    {
        $jak = new JAK8583();
        $date = date('mdHis');

        $jak->addMTI('0200');
        $jak->addData(7, $date);

        $jak->addData(41, $this->Terminal_ID);
        $jak->addData(42, $this->Merchant_ID);


        $jak->addData(3, '840000');
        $jak->addData(4, '000000001600');
        $jak->addData(11, '000002');
        $jak->addData(12, '150615095240');
        $jak->addData(18, '4829');
        $jak->addData(22, '810');
        $jak->addData(37, '516609000002');
        $jak->addData(48, '0079803490');
        $jak->addData(49, '980');
        $jak->addData(102, '1110922885201');
        var_dump($jak->getISO());
        die();

        // $jak->addData(11, 286808);
        // $jak->addData(70, '301');
        
        return $this->sendISO($jak->getISO());
    }
}
