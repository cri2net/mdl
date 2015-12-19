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
            $raw_answer = '';
            while ((microtime(true) - $time) < 10) {
                $raw_answer .= fgets($fp, 1024);
            }

            var_dump(strlen($raw_answer));

            $answer_jak = $this->parseAnswer($raw_answer);
            var_dump($answer_jak->getMTI());
            print_r($answer_jak->getData());
            
            // fclose($fp);
            die();
        }

        die('----die');

        return $response;
    }

    public function parseAnswer($iso)
    {
        $jak = new JAK8583();
        $inp = bin2hex(substr($iso, 4, 16));
        
        if (strlen($inp) >= 16) {
            $primary    = '';
            $secondary  = '';

            for ($i = 0; $i<16; $i++) {
                $primary .= sprintf("%04d", base_convert($inp[$i], 16, 2));
            }
            
            if ($primary[0] == 1 && strlen($inp) >= 16) {
                for ($i=16; $i<32; $i++) {
                    $secondary .= sprintf("%04d", base_convert($inp[$i], 16, 2));
                }
            }
        }

        $bitmap = base_convert($primary . $secondary, 2, 16);

        $iso = substr($iso, 0, 4) . $bitmap . substr($iso, (($secondary == '') ? 12 : 20));
        $jak->addISO($iso);

        return $jak;
    }

    public function makePayment()
    {
        $jak = new JAK8583();
        $date = date('mdHis');

        $jak->addMTI('0200');
        $local_date = date('ymdHis');
        $local_date = '151217161955';

        $jak->addData(3,   '840000');
        $jak->addData(4,   '000000001600');
        $jak->addData(7,   $date);
        $jak->addData(11,  '000008');
        $jak->addData(12,  $local_date);
        $jak->addData(18,  '4900');
        $jak->addData(41,  $this->Terminal_ID);
        $jak->addData(42,  $this->Merchant_ID);
        $jak->addData(49,  '980');
        $jak->addData(102, '10922885201'); // потом номер 10922885201

        return $this->sendISO($jak);
    }
}
