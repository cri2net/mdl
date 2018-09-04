<?php

use cri2net\php_pdo_db\PDO_DB;

class KmdaOrders
{
    const URL = 'https://my2.kyivcity.gov.ua/orders-gateway';
    const DEFAULT_SERVICE_ID = 'cc5f9a92-3252-4aab-9ccb-938719964ec3';

    public static function createOrder($payment_id)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        $user = PDO_DB::row_by_id(User::TABLE, $payment['user_id']);
        $user['openid_data'] = json_decode($user['openid_data']);
        $token = $user['openid_data']->token->access_token;

        $url = self::URL . '/api/services/' . self::getServiceId() . '/orders';

        $data = [
            'statusDate'  => date(DateTime::ISO8601),
            'message'     => [
                'ua' => 'Новий',
                'ru' => 'Новый',
                'en' => 'New',
            ],
            'description' => [
                'ua' => 'Платіж не оплачено',
                'ru' => 'Платёж не оплачен',
                'en' => 'Payment not payed',
            ],
            // 'details' = [], <JSON> // optional, additional data attached to order
        ];

        $response = self::send(json_encode($data, JSON_UNESCAPED_UNICODE), $url, $token);

        $message_to_log = var_export(
            [
                'date'        => date('Y-m-d H:i:s'),
                'timestamp'   => microtime(true),
                'url'         => $url,
                'token'       => $token,
                'data'        => json_encode($data, JSON_UNESCAPED_UNICODE),
                'response'    => $response,
            ],
            true
        );

        self::logResponse($message_to_log, $payment_id);

        if ($response) {

            $decoded = json_decode($response);

            $payment['processing_data'] = (array)(json_decode($payment['processing_data']));
            $payment['processing_data']['openid'] = [];
            $payment['processing_data']['openid']['id'] = $decoded->id;
            $payment['processing_data']['openid']['full_response'] = $response;

            PDO_DB::update(
                [
                    'processing_data' => json_encode($payment['processing_data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                ],
                ShoppingCart::TABLE,
                $payment_id
            );
        }
    }

    public static function logResponse($message, $payment_id)
    {
        $dir = PROTECTED_DIR . "/logs/kmda-orders/" . date('Y/m/d/');

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir . "$payment_id---" . microtime(true) . '.txt';
        error_log($message . "\r\n\r\n", 3, $file);
    }

    public static function getServiceId()
    {
        if (isset($_SESSION['service_id'])) {
            return $_SESSION['service_id'];
        }
        return self::DEFAULT_SERVICE_ID;
    }

    /**
     * Смена статуса платежа на КМДА
     * @param integer $payment_id ID платежа на сайте
     */
    public static function setOrderStatus($payment_id)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);

        if ($payment['status'] == 'timeout') {
            return;
        }

        $user = PDO_DB::row_by_id(User::TABLE, $payment['user_id']);
        $user['openid_data'] = json_decode($user['openid_data']);
        $token = $user['openid_data']->token->access_token;
        $payment['processing_data'] = json_decode($payment['processing_data']);

        $url = self::URL . "/api/orders/{$payment['processing_data']->openid->id}/status/";

        switch ($payment['status']) {

            case 'new':
                $message = [
                    'ua' => 'Новий',
                    'ru' => 'Новый',
                    'en' => 'New',
                ];
                $description = [
                    'ua' => 'Платіж не оплачено',
                    'ru' => 'Платёж не оплачен',
                    'en' => 'Payment not payed',
                ];
                $url .= 'opened';
                break;

            case 'success':
                $message = [
                    'ua' => 'Сплачено',
                    'ru' => 'Оплачено',
                    'en' => 'Success',
                ];
                $description = [
                    'ua' => 'Платіж успішно проведено',
                    'ru' => 'Платёж успешно проведён',
                    'en' => 'Paid successfully',
                ];
                $url .= 'closed';
                break;

            case 'error':
                $message = [
                    'ua' => 'Помилка',
                    'ru' => 'Ошибка',
                    'en' => 'Error',
                ];
                $description = [
                    'ua' => 'Помилка при сплаті',
                    'ru' => 'Ошибка при оплате',
                    'en' => 'An error occurred',
                ];
                $url .= 'canceled';
                break;

            case 'reverse':
                $message = [
                    'ua' => 'Сторновано',
                    'ru' => 'Сторнирован',
                    'en' => 'Reverse',
                ];
                $description = [
                    'ua' => 'Платіж cторновано',
                    'ru' => 'Платёж сторнирован',
                    'en' => 'Payment is reversed',
                ];
                $url .= 'canceled';
                break;
        }

        $data = [
            'statusDate'  => date(DateTime::ISO8601),
            'message'     => $message,
            'description' => $description,
        ];
        $extra_headers = [
            'If-Match: ' . $payment['processing_data']->openid->full_response->version,
        ];

        $response = self::send(json_encode($data, JSON_UNESCAPED_UNICODE), $url, $token, 'PUT', $extra_headers);
    }

    public static function send($data, $url, $token, $http_method = 'POST', $extra_headers = [])
    {
        $headers = [
            'Content-Type: application/json',
            'X-Result-Include: yes',
            'Authorization: Bearer ' . $token,
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
