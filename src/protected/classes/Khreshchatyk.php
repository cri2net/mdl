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
        $this->Terminal_ID = 'XETEST01';
        $this->Merchant_ID = 'XETEST000000001';

        // test terminal & merchant 2
        // $this->Terminal_ID = 'XETEST02';
        // $this->Merchant_ID = 'XETEST000000002';
    }

    public static function get_API_URL($key)
    {
        $urls = [];

        if (stristr(API_URL, 'bank.gioc')) {
            $urls['CARD_BIND_URL'] = '/reports/rwservlet?report=/site_api/get_accbank.rep&destype=Cache&Desformat=xml&cmdkey=rep&cn=';
        } else {
            $urls['CARD_BIND_URL'] = '/reports/rwservlet?report=/home/oracle/reports/site_api/get_accbank.rep&destype=Cache&Desformat=xml&cmdkey=rep&cn=';
        }

        return $urls[$key];
    }

    /**
     * Получение данных о карте по номеру с опциональной проверкой по номеру паспорта и дате рождения
     * Важно! для привязки карты нужно делать проверку, что $pasp_number и $birthday заполнены.
     * @param  string  $card_number Номер карты
     * @param  integer $pasp_number номер паспорта человека, 6 цифр (без серии)
     * @param  string  $birthday    дата рождения человека в формате d.m.Y
     *
     * @return accoc array | false
     */
    public function getCardData($card_number, $pasp_number = '', $birthday = '')
    {
        $url = API_URL . self::get_API_URL('CARD_BIND_URL') . $card_number;
        if (!empty($pasp_number)) {
            $url .= '&pasp=' . $pasp_number . '&datebr=' . $birthday;
        }

        $data = Http::fgets($url);
        $data = iconv('CP1251', 'UTF-8', $data);
        $data = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $data);
        $xml = @simplexml_load_string($data);

        if ($xml === false) {
            return false;
        }

        $result = [];

        foreach ($xml->xpath("//ROW") as $row) {
            return [
                'acc_bank'      => $row->ACCBANK . '',
                'card_state_id' => $row->CARDSTAEID . ''
            ];
        }

        return false;
    }

    private function sendISO($jak_obj)
    {
        $time = microtime(true);
        $iso = $jak_obj->getMTI() . hex2bin($jak_obj->getBitmap()) . implode($jak_obj->getData());
        
        $fp = fsockopen($this->SVFE_host, $this->SVFE_port, $errno, $errstr, 10);
        if (!$fp) {
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
            return false;
        }

        fwrite($fp, $iso);

        $raw_answer = '';
        ini_set('default_socket_timeout', 5);

        while ((microtime(true) - $time) < 10) {
            $raw_answer .= fgets($fp, 1024);
            if ($this->parseAnswer($raw_answer, true)) {
                break;
            }
        }

        fclose($fp);

        return $this->parseAnswer($raw_answer);
    }

    public function parseAnswer($iso, $just_validate = false)
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

        if ($just_validate) {
            return $jak->validateISO();
        }

        return $jak;
    }

    public function makePayment($payment_id)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        if (!$payment || ($payment['processing'] != 'khreshchatyk')) {
            return false;
        }
        $jak = new JAK8583();
        $date = date('mdHis');

        $summ = str_pad($payment['summ_total'] * 100, 10, '0', STR_PAD_LEFT);
        $time = microtime(true);

        $payment['processing_data'] = (array)(@json_decode($payment['processing_data']));
        $acc_bank = $payment['processing_data']['first']->acc_bank;


        $jak->addMTI('0200');
        $local_date = date('ymdHis', $payment['go_to_payment_time']);
        $uid = str_pad($payment['id'] % 1000000, 6, '0', STR_PAD_LEFT);

        $jak->addData(3,   '840000');           // тип обработки "покупка", кажется
        $jak->addData(4,   $summ);              // сумма в копейках
        $jak->addData(7,   $date);              // дата и время отправки сообщения
        $jak->addData(11,  $uid);               // уникальнй номер транзакции
        $jak->addData(12,  $local_date);        // дата и время начала транзакции
        $jak->addData(18,  '4900');             // код типа торговца. 4900 — для коммунальных услуг
        $jak->addData(41,  $this->Terminal_ID); // ID терминала
        $jak->addData(42,  $this->Merchant_ID); // ID мерчанта
        $jak->addData(49,  '980');              // код валюты
        $jak->addData(102, $acc_bank);          // номер счёта в банке

        $result = $this->sendISO($jak);
        $data = $result->getData();

        $request = [
            'timestamp'   => $time,
            'TerminalID'  => $data[41],
            'MerchantID'  => $data[42],
            'TotalAmount' => $payment['summ_total'] * 100,
            'Currency'    => $data[49],
            'OrderID'     => $payment['id'],
            'Rrn'         => $data[37],
            'ProxyPan'    => '',
            'TranCode'    => $data[39],
            'answer_data' => $data,
        ];
        $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
        $payment['processing_data']['requests'][] = $request;

        $arr = [
            'processing_data' => json_encode($payment['processing_data']),
            'status' => (($data[39] == '00') ? 'success' : 'error'),
        ];

        PDO_DB::update($arr, ShoppingCart::TABLE, $payment['id']);
    }

    public function makeTestPayment()
    {
        $jak = new JAK8583();
        $date = date('mdHis');

        $jak->addMTI('0200');
        $local_date = date('ymdHis');
        $uid = str_pad(time() % 86400, 6, '0', STR_PAD_LEFT);

        $jak->addData(3,   '840000');           // тип обработки "покупка", кажется
        $jak->addData(4,   '000000001600');     // сумма в копейках
        $jak->addData(7,   $date);              // дата и время отправки сообщения
        $jak->addData(11,  $uid);               // уникальнй номер транзакции
        $jak->addData(12,  $local_date);        // дата и время начала транзакции
        $jak->addData(18,  '4900');             // код типа торговца. 4900 — для коммунальных услуг
        $jak->addData(41,  $this->Terminal_ID); // ID терминала
        $jak->addData(42,  $this->Merchant_ID); // ID мерчанта
        $jak->addData(49,  '980');              // код валюты
        $jak->addData(102, '10922885201');      // номер счёта в банке

        return $this->sendISO($jak);
    }
}
