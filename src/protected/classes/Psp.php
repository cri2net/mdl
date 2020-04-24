<?php

use cri2net\php_pdo_db\PDO_DB;

class Psp
{
    const PSP_URL      = 'https://fc.gerc.ua:8443/api/main_enter.php?point_in=core_in&action=get_id';
    const PSP_URL_TEST = 'https://fc.gerc.ua:8443/api_test/main_enter.php?point_in=core_in&action=get_id';
    
    const SITE_ID = 41;
    const SITE_ID_TEST = 30;

    const PSP_PRIVATE_KEY      = PROTECTED_DIR . '/conf/payments/psp/_kmda_priv.pem';
    const PSP_PUBLIC_KEY       = PROTECTED_DIR . '/conf/payments/psp/gerc_kmda_pub.pem';
    const PSP_PRIVATE_KEY_TEST = PROTECTED_DIR . '/conf/payments/psp/_kmda_test_priv.pem';
    const PSP_PUBLIC_KEY_TEST  = PROTECTED_DIR . '/conf/payments/psp/gerc_kmda_pub_test.pem';

    public static function sendPaymentToGate($payment_id, $payment_type = 'komdebt', $ext_fields = [])
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        
        if ($payment === null) {
            return false;
        }

        $payment['processing_data'] = (array)@json_decode($payment['processing_data'], true);

        $type_2_id = [
            'edu'     => 22,
            'gai'     => 22,
            'budget'  => 22,
            'asop'    => 2,
            'komdebt' => 2,
            'telekom' => 53,
            'telecom' => 53,
        ];
        $checker_type = [
            'asop'    => 7,
            'komdebt' => 2,
            'edu'     => 31,
            'telecom' => 48,
            'telekom' => 48,
        ];
        $type_id      = (isset($type_2_id[$payment_type]))    ? $type_2_id[$payment_type]    : $payment_type;
        $checker_type = (isset($checker_type[$payment_type])) ? $checker_type[$payment_type] : $type_id;
        $method       = ($payment_type == 'komdebt') ? 'xml' : 'json';
        $site_id      = (defined('PSP_TEST_MODE') && PSP_TEST_MODE) ? self::SITE_ID_TEST : self::SITE_ID;

        $data = [
            'site_id'      => $site_id,
            'type'         => $type_id,
            'checker_type' => $checker_type,
            'order_id'     => $payment_id,
            'method'       => $method,
        ];

        if ($payment_type == 'komdebt') {
            $data['data'] = ShoppingCart::getPaymentXml($payment_id);
        } else {
            foreach ($ext_fields as $key => $value) {
                $data[$key] = $value;
            }
        }

        if (defined('PSP_TEST_MODE') && PSP_TEST_MODE) {
            $Encryptor = new Encryptor(self::PSP_PRIVATE_KEY_TEST, self::PSP_PUBLIC_KEY_TEST);
            $url = self::PSP_URL_TEST;
        } else {
            $Encryptor = new Encryptor(self::PSP_PRIVATE_KEY, self::PSP_PUBLIC_KEY);
            $url = self::PSP_URL;
        }

        $post_data = [
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
        ];
        $post_data['sign'] = $Encryptor->get_sign($post_data['data']);
        $response = Http::httpPost($url . '&site_id=' . $site_id, $post_data, false);

        $tmp = @json_decode($response);
        if (!empty($tmp) && isset($tmp->data)) {

            $tmp->data = json_decode($tmp->data);
            $_id = $tmp->data->answer->oper_id;

        } else {
            $_id = $response;
        }
        $_id = (int)$_id;

        $payment['processing_data']['first']['psp_id'] = $_id;
        if (defined('PSP_TEST_MODE') && PSP_TEST_MODE) {
            $payment['processing_data']['first']['psp_is_test'] = true;
        }

        $to_update = [
            'processing_data' => json_encode($payment['processing_data'], JSON_UNESCAPED_UNICODE),
        ];
        PDO_DB::update($to_update, ShoppingCart::TABLE, $payment_id);

        return $_id;
    }
}
