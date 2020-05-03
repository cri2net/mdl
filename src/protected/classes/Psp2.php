<?php

use cri2net\php_pdo_db\PDO_DB;

class Psp2
{
    const TOKEN_URL_TEST   = 'https://fc-dev.gerc.ua:8443/security/token.php?site_id=';
    const PAYMENT_URL_TEST = 'https://fc-dev.gerc.ua:8443/payframe/index.php?common=get_id&token=';
    const PASSWORD_TEST    = 'test:12345';
    const SITE_ID_TEST     = 4;

    const TOKEN_URL   = 'https://fc.gerc.ua:8443/security/token.php?site_id=';
    const PAYMENT_URL = 'https://fc.gerc.ua:8443/payframe/index.php?common=get_id&token=';
    const PASSWORD    = 'mdl:WEt839o1ISvs';
    const SITE_ID     = 361;

    public static function getToken()
    {
        $time = date('ymdHis');
        
        if (defined('PSP_TEST_MODE') && PSP_TEST_MODE) {
            $url = self::TOKEN_URL_TEST . self::SITE_ID_TEST;
            $hash = hash('sha256', md5(self::PASSWORD_TEST) . $time);
        } else {
            $url = self::TOKEN_URL . self::SITE_ID;
            $hash = hash('sha256', md5(self::PASSWORD) . $time);
        }

        $extra_headers = [
            'Request_Time: ' . $time,
            'Authorization: ' . $hash,
        ];

        $response = Http::httpGet($url, false, true, $extra_headers);
        $result = @json_decode($response, true);

        if (!empty($result['error'])) {
            throw new Exception($result['error']);
        }

        return (empty($result['access_token'])) ? null : $result['access_token'];
    }

    public static function initPayment($payment_id, $token, $payment_type, $ext_fields)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        if ($payment === null) {
            return false;
        }

        if (defined('PSP_TEST_MODE') && PSP_TEST_MODE) {
            $url = self::PAYMENT_URL_TEST . $token;
            $site_id = self::SITE_ID_TEST;
        } else {
            $url = self::PAYMENT_URL . $token;
            $site_id = self::SITE_ID;
        }

        $type2id = [
            'asop'    => 7,
            'komdebt' => 2,
        ];

        $data = [
            'site_id'      => $site_id,
            'order_id'     => $payment_id,
            'amount'       => $payment['summ_plat'] * 100,
            'payment_type' => $type2id[$payment_type],
            'backref'      => BASE_URL . '/cabinet/payments/details/'. $payment['id'] .'/',
            'notify'       => BASE_URL . '/payments-notify/psp2/',
        ];

        if ($payment_type == 'komdebt') {
            $data['payment_data'] = ShoppingCart::getPaymentXml($payment_id);
        } else {
            foreach ($ext_fields as $key => $value) {
                $data[$key] = $value;
            }
        }

        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $response = self::sendJSON($url, $data);
        $result = json_decode($response, true);

        return $result;
    }

    public static function sendJSON($url, $data)
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
            CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json",
            ],
        );
        
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function sendPaymentToGate($payment_id, $payment_type = 'komdebt', $ext_fields = [])
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        if ($payment === null) {
            return false;
        }

        $token = self::getToken();
        $data = self::initPayment($payment_id, $token, $payment_type, $ext_fields);
        $payment['processing_data'] = (array)@json_decode($payment['processing_data'], true);

        if (empty($data['oper_id'])) {
            return;
        }

        $payment['processing_data']['first']['psp_id'] = $data['oper_id'];
        if (defined('PSP_TEST_MODE') && PSP_TEST_MODE) {
            $payment['processing_data']['first']['psp_is_test'] = true;
        }

        $to_update = [
            'processing_data' => json_encode($payment['processing_data'], JSON_UNESCAPED_UNICODE),
        ];
        PDO_DB::update($to_update, ShoppingCart::TABLE, $payment_id);

        if ($data['oper_id'] > 0) {
            if (empty($_SESSION['psp_all_ids'])) {
                $_SESSION['psp_all_ids'] = [];
            }
            $_SESSION['psp_all_ids'][] = $payment_id;
        }

        return $data['oper_id'];
    }
}
