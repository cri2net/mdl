<?php

class TasLink
{
    const PASSWORD_INIT_SESSION = 'TASLINK_ORDER_REQ';
    const PASSWORD_MAKE_PAYMEENT = 'TASLINK_FIN_REQ';
    const PASSWORD_FIN_RESPONSE = 'TASLINK_FIN_RESP';
    const PASSWORD_STATUS_REQ = 'TASLINK_STATUS_REQ';
    const PASSWORD_STATUS_RESP = 'TASLINK_STATUS_RESP';
    const PASSWORD_BATCH_REQ = 'TASLINK_BATCH_REQ';
    const IFRAME_SRC = 'https://gerc-payments.taslink.com.ua/gioc.html?oid=';

    public $session_id;
    public $payment_id;
    public $payment_type;
    
    public function __construct($type = 'komdebt')
    {
        $this->payment_type = $type;
    }

    public static function get_API_URL($key)
    {
        $urls = [];
        $urls['INIT_SESSION']  = 'https://gerc-payments.taslink.com.ua/getOrder.php';
        $urls['MAKE_PAYMEENT'] = 'https://gerc-payments.taslink.com.ua/finAction.php';
        $urls['CHECK_STATUS']  = 'https://gerc-payments.taslink.com.ua/getStatus.php';
        $urls['GET_BATCH']     = 'https://gerc-payments.taslink.com.ua/getBatch.php';

        return $urls[$key];
    }

    public function checkNotifySignature($json)
    {
        $real_sign = md5(strtoupper(strrev($json->ORDERID) . strrev($json->TRANID) . strrev($json->AMOUNT) . strrev(self::PASSWORD_FIN_RESPONSE)));
        return (strcasecmp($real_sign, $json->SIGN) == 0);
    }

    public function getTermname()
    {
        // тестовый терминал
        // return '000000020000001';
        
        switch ($this->payment_type) {
            case 'komdebt': return 'TE0020';
            case 'budget':  return 'TE0021';
        }
    }

    /**
     * Инициализация сессии для дальнейшего проброса человека на фрейм
     * @param  integer | string $payment_id ID платежа в системе ГЕРЦ
     * @param  string           $type       тип транзакции. OPTIONAL
     * @return string                       id сессии
     */
    public function initSession($payment_id, $type = 'Purchase')
    {
        $payment_id = "gioc-$payment_id";
        $this->payment_id = $payment_id;
        $termname = $this->getTermname();
        $str_to_sign = strrev($payment_id) . strrev($termname) . strrev($type);
        $sign = md5(strtoupper($str_to_sign . strrev(self::PASSWORD_INIT_SESSION)));

        $data = [
            'tranid'   => $payment_id, // уникальный идентификатор в системе Герц
            'termname' => $termname,   // имя терминала в платежном шлюзе Тас Линк. Оно же тег (для определения типа платежа)
            'type'     => $type,       // тип операции
            'sign'     => $sign,       // signature
        ];

        $url = self::get_API_URL('INIT_SESSION');
        $session_id = self::httpPost($url, json_encode($data));

        $processing_data = [
            'first' => [
                'oid'      => $session_id,
                'termname' => $termname,
                'type'     => $type
            ]
        ];
        $arr = [
            'processing_data' => json_encode($processing_data)
        ];

        PDO_DB::update($arr, ShoppingCart::TABLE, str_replace('gioc-', '', $payment_id));
        $this->session_id = $session_id;
        return $session_id;
    }

    public function checkStatus($payment_id)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        $payment['processing_data'] = json_decode($payment['processing_data']);
        if (!isset($payment['processing_data']->cron_check_status)) {
            $payment['processing_data']->cron_check_status = [];
        }

        $type = 'GetStatus';
        $oid = $payment['processing_data']->first->oid;
        $termname = $payment['processing_data']->first->termname;
        $sign = md5(strtoupper(strrev($type) . strrev($termname) . strrev($oid) . strrev(self::PASSWORD_STATUS_REQ)));

        $send_data = [
            'type'     => $type,     // тип операции
            'termname' => $termname, // имя терминала в платежном шлюзе Тас Линк
            'oid'      => $oid,      // ордер, в рамках которого было иниццировано платеж  
            'sign'     => $sign,     // signature
        ];

        $url = self::get_API_URL('CHECK_STATUS');
        $response = self::httpPost($url, json_encode($send_data));
        $response = @json_decode($response);
        $data = [];

        try {
            if ($response === null) {
                throw new Exception("Invalid JSON");
            }

            $real_sign = md5(strtoupper(strrev($response->oid) . strrev($response->respcode) . strrev($response->tranid) . strrev(self::PASSWORD_STATUS_RESP)));
            if (strcasecmp($real_sign, $response->sign) !== 0) {
                throw new Exception("Error Signature");
            }

            if ($response->respcode.'' == '') {
                throw new Exception("Транзакция не завершена");
            }

            $success = in_array($response->respcode, array('000', '001'));
            if ($success) {
                $data['status'] = 'success';
            } else {
                $data['status'] = 'error';
            }
            
            $payment['processing_data']->cron_check_status[] = [
                'timestamp' => microtime(true),
                'raw_data' => $response,
                'request' => $send_data
            ];
        } catch (Exception $e) {
            $discard = (microtime(true) - $payment['paytime'] > 1800);

            if ($discard) {
                $data['status'] = 'timeout';
                $data['send_payment_status_to_reports'] = 1;

                $payment['processing_data']->cron_check_status[] = [
                    'timestamp' => microtime(true),
                    'raw_data' => $response,
                    'request' => $send_data
                ];
            }
        }

        if (!empty($data)) {
            $data['processing_data'] = json_encode($payment['processing_data']);
            PDO_DB::update($data, ShoppingCart::TABLE, $payment_id);
            ShoppingCart::send_payment_status_to_reports($payment_id);
        }
    }

    public function makePayment($amount, $commission)
    {
        $total = $amount + $commission;
        $str_to_sign = strrev($this->payment_id) . strrev($this->session_id) . strrev($total);
        $sign = md5(strtoupper($str_to_sign . strrev(self::PASSWORD_MAKE_PAYMEENT)));

        $data = [
            'tranid' => $this->payment_id, // уникальный идентификатор в системе Герц
            'oid'    => $this->session_id, // уникальный идентификатор сессии (из 3-го шага)
            'amount' => $amount,           // сумма платежа без комиссии
            'fee'    => $commission,       // комиссия
            'total'  => $total,            // общая сумма
            'sign'   => $sign,             // signature
        ];

        $url = self::get_API_URL('MAKE_PAYMEENT');
        $response = self::httpPost($url, json_encode($data));
        if ($response == 'Request accepted') {
            return true;
        }

        throw new Exception($response);
        return false;
    }

    public function getBatch($date = null)
    {
        if ($date == null) {
            $date = date('dmY');
        }
        $type = 'getBatch';
        $sign = md5(strtoupper(strrev($type) . strrev($date) . strrev(self::PASSWORD_BATCH_REQ)));
        $url = self::get_API_URL('GET_BATCH');

        $data = [
            'type'    => $type,
            'batchno' => $date, // день в формате DDMMYYYY, напр. 01012016
            'sign'    => $sign, // signature
        ];

        $response = self::httpPost($url, json_encode($data));

        return $response;
    }

    /**
     * Отправка запроса на сервер ТАС.
     * Реализация отличается от метода в классе Http, целесообразно частично продублировать код в этом классе
     * @param  string  $url   URL to POST
     * @param  string  $data  final data
     * @return string         raw answer
     */
    public static function httpPost($url, $data)
    {
        $ch = curl_init();

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => array(
                'Content-Length: ' . strlen($data)
            )
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
