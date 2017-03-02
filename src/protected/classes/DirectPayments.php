<?php

use cri2net\php_pdo_db\PDO_DB;

class DirectPayments
{
    const PPP_URL_CREATE = '/reports/rwservlet?report=gerc_api/api_pnew_one_plat.rep&cmdkey=api_test';
    const CHECK_MFO = '1371337137137137137';

    /**
     * Проверка соответсвия МФО и расчётного счёта
     * @param  string $mfo    МФО - число
     * @param  string $rcount Расчётный счёт - число
     * @return boolean
     */
    public static function checkMfo($mfo, $rcount)
    {
        $mfo = preg_replace('/[^0-9]/', '', $mfo);
        $rcount = preg_replace('/[^0-9]/', '', $rcount);
        
        if (strlen($rcount) > 14) {
            return false;
        }
        
        $const = self::CHECK_MFO;
        $sum = 0;

        // строка для проверки
        $check_str = substr($mfo, 0, 5) . $rcount;

        for ($i=0; $i < min(strlen($check_str), strlen($const)); $i++) {
            
            // цифра контрольного разряда не участвует
            if ($i <> 9) {

                // перемножаем поразрядно
                $tmp = $check_str[$i] * $const[$i];

                if ($tmp > 9) {
                    // учитываем только последний разряд
                    $tmp = substr($tmp, 1);
                }

                // складываем перемноженное
                $sum += $tmp;
            }
        }


        // добавляем длину расчётного счёта
        $sum += strlen($rcount);


        // берём последний разряд суммы
        $sum .= '';
        $sum = $sum[strlen($sum) - 1];

        // всегда умножаем на 7
        $sum *= 7;

        // последний разряд - проверочный
        $sum .= '';
        $res = $sum[strlen($sum) - 1];

        return ($res == substr($rcount, 4, 1));
    }
    
    /**
     * Создание платежа в оракле на разовый ручной платёж
     * 
     * @param  integer $summ       Сумма платежа в копейках
     * @param  integer $user_id    id пользователя в системе сайта. Любой
     * @param  string  $r1         ФИО плательщика
     * @param  string  $r2         АДРЕС плательщика
     * @param  string  $r3         ИНН плательщика
     * @param  string  $r81        Телефон
     * @param  string  $r141       МФО
     * @param  string  $r142       БАНК ПОЛУЧАТЕЛЯ
     * @param  string  $r143       Р/СЧЕТ
     * @param  string  $r144       ОКПО
     * @param  string  $r145       НАЗНАЧЕНИЕ ПЛАТЕЖА
     * @param  string  $r161       ПОЛУЧАТЕЛЬ
     *
     * @return void
     */
    public static function pppCreatePayment($summ, $user_id, $r1, $r2, $r3, $r81, $r141, $r142, $r143, $r144, $r145, $r161)
    {
        ShoppingCart::pppGetCashierByProcessing('tas', $login, $password);

        $url = API_URL . self::PPP_URL_CREATE . '&login=' . $login . '&pwd=' . $password;

        $tmp_user_id = ($user_id > 0) ? $user_id : 1;
        $timestamp = microtime(true);

        $url .= '&summ=' . $summ;
        $url .= '&idsiteuser=' . $tmp_user_id;
        $url .= '&r1='    . rawurlencode(iconv('UTF-8', 'CP1251', $r1));
        $url .= '&r2='    . rawurlencode(iconv('UTF-8', 'CP1251', $r2));
        $url .= '&r3='    . rawurlencode(iconv('UTF-8', 'CP1251', $r3));
        $url .= '&r81='   . rawurlencode(iconv('UTF-8', 'CP1251', $r81));
        $url .= '&r141='  . rawurlencode(iconv('UTF-8', 'CP1251', $r141));
        $url .= '&r142='  . rawurlencode(iconv('UTF-8', 'CP1251', $r142));
        $url .= '&r143='  . rawurlencode(iconv('UTF-8', 'CP1251', $r143));
        $url .= '&r144='  . rawurlencode(iconv('UTF-8', 'CP1251', $r144));
        $url .= '&r145='  . rawurlencode(iconv('UTF-8', 'CP1251', $r145));
        $url .= '&r161='  . rawurlencode(iconv('UTF-8', 'CP1251', $r161));

        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);

        $message_to_log = var_export(
            [
                'date'        => date('Y-m-d H:i:s'),
                'timestamp'   => microtime(true),
                'reports_url' => $url,
                'answer'      => $xml_string,
            ],
            true
        );

        if ($xml === false) {
            $error_str = 'Некоректний XML';
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports_new/direct');
            throw new Exception($error_str);
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;
        $err = $row_elem->ERR.'';

        if ($err != '0') {
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports_new/direct');
            throw new Exception(UPC::get_error($err));
        }

        $insert = [
            'user_id'                 => $user_id,
            'acq'                     => $row_elem->ACQ.'',
            'timestamp'               => $timestamp,
            'type'                    => 'direct',
            'count_services'          => 1,
            'processing'              => 'tas',
            'summ_komis'              => floatval($row_elem->SUMM_KOMIS.'') / 100,
            'summ_total'              => floatval($row_elem->SUMM_TOTAL.'') / 100,
            'reports_id_plat_klient'  => $row_elem->ID_PLAT_KLIENT.'',
            'send_payment_to_reports' => 1,
            'ip'                      => USER_REAL_IP,
            'user_agent_string'       => HTTP_USER_AGENT,
        ];
        $insert['summ_plat'] = round($insert['summ_total'] - $insert['summ_komis'], 2);

        $payment_id = PDO_DB::insert($insert, ShoppingCart::TABLE);
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        ShoppingCart::logRequestToReports($message_to_log, $payment_id, true, 'new', 'reports_new/direct');

        $data = [
            'r1'   => $r1,
            'r2'   => $r2,
            'r3'   => $r3,
            'r81'  => $r81,
            'r141' => $r141,
            'r142' => $r142,
            'r143' => $r143,
            'r144' => $r144,
            'r145' => $r145,
            'r161' => $r161,

            'fio'        => $r1,
            'address'    => $r2,
            'phone'      => $r81,
            'dst_mfo'    => $r141,
            'dst_rcount' => $r143,
            'dst_okpo'   => $r144,
        ];

        $xml_fields = ['OUTBANK', 'NAME_OWNER_BANK', 'NAME_BANK', 'NAME_PLAT', 'DST_NAME', 'DST_NAME_BANK', 'DEST'];

        for ($i=0; $i < count($xml_fields); $i++) {
            $field = $xml_fields[$i];
            $var = '_' . strtolower($field);
            $$var = $row_elem->$field . '';
            $data[strtolower($field)] = $$var;
        }

        $service = [
            'payment_id' => $payment['id'],
            'user_id'    => $payment['user_id'],
            'sum'        => $payment['summ_plat'],
            'timestamp'  => $timestamp,
            'data'       => json_encode($data, JSON_UNESCAPED_UNICODE),
        ];
        PDO_DB::insert($service, ShoppingCart::SERVICE_TABLE);

        return $payment;
    }
}
