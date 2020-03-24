<?php

use cri2net\php_pdo_db\PDO_DB;

class KmdaP2P
{
    const APP_USER = 'p2p_gerc_m_ab';
    const PASSWORD = '5D1CFA0AF3B218C3CBEED93844D934A7BF08CC36';
    const URL_INIT = 'https://fc.gerc.ua:8443/p2p/p2p.php';
    const URL_PROV = 'https://fc.gerc.ua:8443/p2p/prov_plat.php';
    const URL_CALC = 'https://fc.gerc.ua:8443/p2p/calc_comis.php';

    public static function createPayment($pan, $month, $year, $amount, $cvv, $pan_dest, $cell_phone, $target_cell_phone, $user_id)
    {
        $comis = self::calc($pan, $pan_dest, $amount);

        $insert = [
            'user_id'           => (int)$user_id,
            'timestamp'         => microtime(true),
            'summ_plat'         => (int)($amount * 100),
            'summ_komis'        => (int)($comis * 100),
            'summ_total'        => (int)(($amount + $comis) * 100),
            'ip'                => USER_REAL_IP,
            'user_agent_string' => HTTP_USER_AGENT,
            'acq'               => 20,
            'count_services'    => 1,
            'type'              => 'p2p',
        ];

        $payment_id = PDO_DB::insert($insert, TABLE_PREFIX.'payment');

        @KmdaOrders::createOrder($payment_id);

        $data = [
            'p_app_user'          => self::APP_USER,
            'p_password'          => self::PASSWORD,
            'p_summ'              => (int)($amount * 100),
            'p_id_site_user'      => (int)$user_id,
            'p_card_number'       => $pan,
            'p_expire_date'       => "{$year}-{$month}-01",
            'p_cvv'               => $cvv,
            'p_target'            => $pan_dest,
            'p_cell_phone'        => $cell_phone,
            'p_post_back_url'     => BASE_URL.'/p2p-confirm/?id='.$payment_id,
            'p_target_cell_phone' => $target_cell_phone,
        ];

        $response = self::httpPost(self::URL_INIT, json_encode($data));
        $response = @json_decode($response, true);

        if (!empty($response['err_message'])) {
            throw new Exception($response['err_message']);
        }

        if (($response['err']) != null) {
            throw new Exception("Помилка проведення платежу");
        }

        unset($data['p_cvv'], $data['p_expire_date']);
        $data['p_card_number'] = substr_replace($data['p_card_number'], '***', 2, 10);
        $data['p_target'] = substr_replace($data['p_target'], '***', 2, 10);

        $payment_data = [
            'first'    => $data,
            'response' => $response,
        ];

        $upd = [
            'processing_data'        => json_encode($payment_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'reports_id_plat_klient' => @$response['id_plat_klient'],
            'summ_total'             => @$response['summ_total'],
            'summ_komis'             => @$response['summ_total'] - ($amount * 100),
            'reports_id_pack'        => @$response['id_pack'],
        ];
        PDO_DB::update($upd, TABLE_PREFIX.'payment', $payment_id);

        $result = [
            'payment_id' => (int)$payment_id,
            'commission' => $comis,
            'type'       => (isset($response['prompt_code'])) ? '2ds' : '3ds',
        ];
        if ($result['type'] == '3ds') {
            $result['url'] = BASE_URL.'/p2p-redirect?id='.$payment_id;
        }
        else {
            $result['prompt_code'] = $response['prompt_code'];
        }

        return $result;
    }

    public static function calc($pan, $pan_dest, $amount)
    {
        $data = [
            'p_card_number' => $pan,
            'p_target'      => $pan_dest,
            'p_summ'        => $amount * 100,
        ];

        $response = self::httpPost(self::URL_CALC, json_encode($data));
        $response = @json_decode($response, true);

        if (!empty($response['err_message'])) {
            throw new Exception($response['err_message']);
        }

        return (double)$response['comis'];
    }

    public static function httpPost($url, $data, $extra_headers = [])
    {
        $extra_headers[] = 'Content-Type: application/json';
        $ch = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $extra_headers,
        ];

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function confirm($payment_id, $additional)
    {
        $payment = PDO_DB::row_by_id(TABLE_PREFIX.'payment', $payment_id);

        $payment['processing_data'] = json_decode($payment['processing_data'], true);
        $response = @json_decode($payment['processing_data']['response'], true);

        if (empty($response)) {
            return;
        }

        $data = [
            'p_app_user'       => self::APP_USER,
            'p_password'       => self::PASSWORD,
            'p_id_plat_klient' => $payment['reports_id_plat_klient'],
            'p_order_id'       => $payment_id,
            'p_message_id'     => $response['messageId'],
        ];

        foreach ($additional as $key => $value) {
            $data[$key] = $value;
        }

        $response = self::httpPost(self::URL_PROV, json_encode($data));
        $response = @json_decode($response, true);

        $processing_data = (array)@json_decode($payment['processing_data'], true);
        $processing_data['result'] = $response;

        $upd = [
            'processing_data' => json_encode($processing_data, JSON_UNESCAPED_UNICODE),
            'summ_total'      => $response['total_Amount'] * 100,
            'summ_komis'      => ($response['total_Amount'] * 100) - $payment['summ_plat'],
            'status'          => (empty($response['terminal_Id'])) ? 'error' : 'success',
        ];

        if ($upd['status'] == 'success') {
            ShoppingCart::sendFirstPDF($payment['id']);
            KmdaOrders::setOrderStatus($payment['id']);
        }

        PDO_DB::update($upd, TABLE_PREFIX.'payment', $payment_id);
    }

    public static function logResponse($message, $payment_id)
    {
        $dir = PROTECTED_DIR."/logs/kmda-orders/".date('Y/m/d/');

        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        $file = $dir."$payment_id---".microtime(true).'.txt';
        @error_log($message."\r\n\r\n", 3, $file);
    }

    public static function send($data, $url, $token, $http_method = 'POST', $extra_headers = [])
    {
        $headers = [
            'Content-Type: application/json',
            'X-Result-Include: yes',
            'Authorization: Bearer '.$token,
        ];
        foreach ($extra_headers as $extra_header) {
            $headers[] = $extra_header;
        }

        $context = stream_context_create([
            'http' => [
                'method'  => $http_method,
                'header'  => $headers,
                'content' => $data,
            ],
        ]);

        return file_get_contents($url, false, $context);
    }
}
