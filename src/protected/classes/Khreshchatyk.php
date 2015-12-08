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
        
        // test terminal & merchant 1
        $this->Terminal_ID = XETEST01;
        $this->Merchant_ID = XETEST000000001;

        // test terminal & merchant 2
        // $this->Terminal_ID = XETEST02;
        // $this->Merchant_ID = XETEST000000002;
    }

    public function checkBalance()
    {
        $jak = new JAK8583();
        $date = date('mdHis');

        $jak->addMTI('0200');
        $jak->addData(7, $date);

        $jak->addData(41, $this->Terminal_ID);
        $jak->addData(42, $this->Merchant_ID);

        // $jak->addData(11, 286808);
        // $jak->addData(70, '301');
    }
}
