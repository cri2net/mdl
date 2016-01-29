<?php

class Khreshchatyk
{
    protected $SVFE_host;
    protected $SVFE_port;
    protected $Terminal_ID;
    protected $Merchant_ID;

    public function __construct()
    {
        $this->setEnvironment();
    }

    public function setEnvironment($work_env = true)
    {
        if ($work_env) {
            $this->SVFE_host = '10.192.1.246';
            $this->SVFE_port = 12350;

            // work terminal & merchant
            $this->Terminal_ID = 'XE010006';
            $this->Merchant_ID = 'XE0101000000006';

            return;
        }
    
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
        // молча обрезаем ведущие нули в номере карты.
        $card_number = ltrim($card_number, '0');

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

        while ((microtime(true) - $time) < 5) {
            $raw_answer .= fgets($fp, 129);
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

    /**
     * Генерация, отправка и обработака ответа запроса на списание денег
     * @param  integer $payment_id ID платежа в БД сайта
     * @return return void
     */
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
        $jak->addData(48,  '9803491');          // Tag 98 (Service ID = 491), этот ID закреплён за ГИОЦ.
        $jak->addData(49,  '980');              // код валюты
        $jak->addData(102, $acc_bank);          // номер счёта в банке

        try {
            $need_reverse = false;
            $result = $this->sendISO($jak);
            $data = $result->getData();
        } catch (Exception $e) {
            $need_reverse = true;
        }

        $request = [
            'timestamp'    => $time,
            'TotalAmount'  => $payment['summ_total'] * 100,
            'STAN'         => $data[11],
            'Rrn'          => $data[37],
            'ApprovalCode' => $data[38],
            'TranCode'     => $data[39],
            'TerminalID'   => $data[41],
            'MerchantID'   => $data[42],
            'Currency'     => $data[49],
            'OrderID'      => $payment['id'],
            'ProxyPan'     => '',
            'answer_data'  => $data,
        ];
        $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
        $payment['processing_data']['requests'][] = $request;

        $arr = [
            'processing_data' => json_encode($payment['processing_data']),
            'status' => (($data[39] == '00') ? 'success' : 'error'),
        ];

        PDO_DB::update($arr, ShoppingCart::TABLE, $payment['id']);

        if ($need_reverse) {
            $this->reversesTransaction($payment['id']);
        }
    }

    public function reversesTransaction($payment_id, $first = true)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        if (!$payment || ($payment['processing'] != 'khreshchatyk')) {
            return false;
        }
        $payment['processing_data'] = (array)(@json_decode($payment['processing_data']));
        if (!$payment['processing_data']) {
            return false;
        }

        $jak = new JAK8583();
        $date = date('mdHis');

        $summ = str_pad($payment['processing_data']['requests'][0]->TotalAmount, 10, '0', STR_PAD_LEFT);
        $time = microtime(true);

        $acc_bank = $payment['processing_data']['first']->acc_bank;

        $mti = ($first) ? '0400' : '0401';
        $jak->addMTI($mti);
        $local_date = date('ymdHis', $payment['go_to_payment_time']);
        $uid = str_pad($payment['id'] % 1000000, 6, '0', STR_PAD_LEFT);

        // определяем данные для запроса. Некоторые данные мы могли не получить, тогда пишем те, что кажутся правильными

        $ApprovalCode = ($payment['processing_data']['requests'][0]->ApprovalCode != '') ? $payment['processing_data']['requests'][0]->ApprovalCode : '';
        $TerminalID   = ($payment['processing_data']['requests'][0]->TerminalID != '')   ? $payment['processing_data']['requests'][0]->TerminalID   : $this->Terminal_ID;
        $MerchantID   = ($payment['processing_data']['requests'][0]->MerchantID != '')   ? $payment['processing_data']['requests'][0]->MerchantID   : $this->Merchant_ID;
        $Currency     = ($payment['processing_data']['requests'][0]->Currency != '')     ? $payment['processing_data']['requests'][0]->Currency     : '980';
        $Rrn          = ($payment['processing_data']['requests'][0]->Rrn != '')          ? $payment['processing_data']['requests'][0]->Rrn          : '';
        $ApprovalCode = str_pad($ApprovalCode, 6, '0', STR_PAD_LEFT); // если поля нет, заполняем нулями
        $Rrn          = str_pad($Rrn, 12, ' ', STR_PAD_LEFT); // если поля нет, заполняем пробелами

        $jak->addData(3,   '840000');                                       // тип обработки "покупка", кажется
        $jak->addData(4,   $summ);                                          // сумма в копейках
        $jak->addData(7,   $date);                                          // дата и время отправки сообщения
        $jak->addData(11,  $uid);                                           // уникальнй номер транзакции
        $jak->addData(12,  $local_date);                                    // дата и время начала транзакции
        $jak->addData(18,  '4900');                                         // код типа торговца. 4900 — для коммунальных услуг
        $jak->addData(37,  $Rrn);                                           // Retrieval Reference Number
        $jak->addData(38,  $ApprovalCode);                                  // Код подтверждения
        $jak->addData(41,  $TerminalID);                                    // ID терминала
        $jak->addData(42,  $MerchantID);                                    // ID мерчанта
        $jak->addData(49,  $Currency);                                      // код валюты
        $jak->addData(102, $payment['processing_data']['first']->acc_bank); // номер счёта в банке

        $result = $this->sendISO($jak);
        $data = $result->getData();

        $request = [
            'timestamp'    => $time,
            'mti'          => $mti,
            'ProcessType'  => $data[3],
            'TotalAmount'  => $data[4],
            'DateTime'     => $data[7],
            'STAN'         => $data[11],
            'MerchantType' => $data[18],
            'Rrn'          => $data[37],
            'ApprovalCode' => $ApprovalCode,
            'TranCode'     => $data[39],
            'TerminalID'   => $data[41],
            'MerchantID'   => $data[42],
            'Currency'     => $data[49],
            'answer_data'  => $data,
            'request_data' => [
                '3'   => '840000',
                '4'   => $summ,
                '7'   => $date,
                '11'  => $uid,
                '12'  => $local_date,
                '18'  => '4900',
                '37'  => $Rrn,
                '38'  => $ApprovalCode,
                '41'  => $TerminalID,
                '42'  => $MerchantID,
                '49'  => $Currency,
                '102' => $payment['processing_data']['first']->acc_bank
            ]
        ];
        $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
        $payment['processing_data']['requests'][] = $request;

        $arr = [
            'status'                         => 'error',
            'processing_data'                => json_encode($payment['processing_data']),
            'send_payment_status_to_reports' => 0,
        ];

        PDO_DB::update($arr, ShoppingCart::TABLE, $payment['id']);

        if ((strlen($data) == 0) && $first) {
            $this->reversesTransaction($payment['id'], false);
        }
    }

    public function makeTestPayment()
    {
        $this->setEnvironment(false);
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
        $jak->addData(48,  '9803491');          // Tag 98 (Service ID = 491), этот ID закреплён за ГИОЦ.
        $jak->addData(49,  '980');              // код валюты
        $jak->addData(102, '10922885201');      // номер счёта в банке

        $answer = $this->sendISO($jak);
        print_r($answer->getData());
    }
}
