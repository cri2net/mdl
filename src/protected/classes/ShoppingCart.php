<?php

use cri2net\php_pdo_db\PDO_DB;

class ShoppingCart
{
    const TABLE                   = DB_TBL_PAYMENT;
    const SERVICE_TABLE           = DB_TBL_PAYMENT_SERVICES;
    const REPORT_BASE_URL         = '/reports/rwservlet';
    const PDF_TODAY_URL           = '/reports/rwservlet?report=/ppp/kv_www_all.rep&destype=Cache&Desformat=pdf&cmdkey=api_kmda_site&id_p=';
    const PDF_NOT_TODAY_URL       = '/reports/rwservlet?report=/ppp/kv_www_hist.rep&destype=Cache&Desformat=pdf&cmdkey=api_kmda_site&id_k=';

    const PDF_NEW_CURRENT_KOM     = '/reports/rwservlet?report=ppp/kv_www_all_sity_new27.rep&cmdkey=rep&destype=cache&desformat=pdf&id_p=';
    const PDF_NEW_CURRENT_INSTANT = '/reports/rwservlet?report=ppp/kv_www_singl_sity_new27.rep&cmdkey=rep&destype=cache&desformat=pdf&id_p=';
    const PDF_NEW_HISTORY_KOM     = '/reports/rwservlet?report=ppp/kv_www_all_Hist_new33.rep&cmdkey=rep&destype=cache&desformat=pdf&id_k=';
    const PDF_NEW_HISTORY_INSTANT = '/reports/rwservlet?report=ppp/kv_www_Singl_Hist_new33.rep&cmdkey=rep&destype=cache&desformat=pdf&id_k=';

    public static function getActivePaySystems($get_all_supported_paysystems = false)
    {
        return ($get_all_supported_paysystems)
            ? ['psp', 'psp-2']
            : ['psp', 'psp-2'];
    }

    /**
     * Получаем логин и хеш пароля кассира
     * @param  string $processing Ключ процессинга
     * @param  string $login      Логин кассира (SHA1, кажется)
     * @param  string $password   Хеш пароля кассира
     * 
     * @return void
     */
    public static function pppGetCashier($processing, &$login, &$password)
    {
        switch ($processing) {
            case 'tas':
                $login    = 'SITE_KMDA_TAS';
                $password = '1B842ABA1CC93A92E761CA10090AEFAD6944A5DF';
                break;
        }
    }
    
    public static function logRequestToReports($message, $payment_id, $success = true, $type = 'new', $folder = 'reports_new')
    {
        $dir = PROTECTED_DIR . "/logs/$folder/$type/" . date('Y/m/d/');

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . "$payment_id---" . microtime(true) . '.txt';
        error_log($message . "\r\n\r\n", 3, $file);

        if (!$success) {
            $file = PROTECTED_DIR . "/logs/$folder/$type/with_error.txt";
            error_log("payment_id = $payment_id\r\n" . $message . "\r\n\r\n", 3, $file);
        }
    }

    public static function getPercentRule($pay_system = null)
    {
        // пример правила: ['percent' => 2, 'min' => 2, 'big_after' => 1000, 'big_percent' => 3.5]
        
        $rules = [
            'tas'        => ['percent' => 2, 'min' => 5],
            'psp'        => ['percent' => 2, 'min' => 5],
            'oschad'     => ['percent' => 0, 'min' => 0],
            'oschadbank' => ['percent' => 2, 'min' => 5],
        ];

        if ($pay_system) {
            $rules = $rules[$pay_system];
        }

        return $rules;
    }

    public static function getPercent($sum)
    {
        $sum = (double)(str_replace(",", ".", $sum));
        $rules = self::getPercentRule();

        foreach ($rules as $key => $value) {
            if (isset($rules['big_after']) && (isset($rules['big_after']) <= $sum)) {
                $rules[$key]['percent'] = $rules[$key]['big_percent'];
            }
        }

        return $rules;
    }

    public static function getPercentSum($sum, $type)
    {
        $sum = (double)(str_replace(",", ".", $sum));
        $rules = self::getPercent($sum);

        if (isset($rules[$type])) {
            return sprintf('%.2f', max(round($sum * $rules[$type]['percent'] / 100, 2), $rules[$type]['min']));
        }

        throw new Exception("UNKNOW TYPE $type");
    }

    public static function getTotalDebtSum($data)
    {
        $sum = 0;
        foreach ($data['items'] as $item) {
            $servicePostSum = str_replace(",", ".", $data[$item."_sum"]);
            if ((float)$servicePostSum <= 0) {
                continue;
            }
            $sum += $servicePostSum;
        }
        $sum = sprintf('%.2f',$sum);
        return $sum;
    }

    public static function add($data, $user_id)
    {
        $summ_plat = 0;
        $real_servises = [];
        $timestamp = microtime(true);

        if (count($data['items']) > 0) {
            foreach ($data['items'] as $item) {
                $tmp_sum = (float)str_replace(",", ".", $data[$item."_sum"]);
                if ($tmp_sum > 0) {
                    $summ_plat += $tmp_sum;
                    $real_servises[] = $item;
                }
            }
        }

        if (count($real_servises) == 0) {
            throw new Exception(ERROR_EMPTY_KOMDEBT_PAYMENT);
            return false;
        }

        $payment_data = [
            'user_id'           => $user_id,
            'acq'               => '',
            'timestamp'         => $timestamp,
            'type'              => 'komdebt',
            'flat_id'           => $data['flat_id'],
            'city_id'           => $data['city_id'],
            'count_services'    => count($real_servises),
            'summ_plat'         => $summ_plat,
            'summ_komis'        => 0,
            'summ_total'        => $summ_plat,
            'ip'                => USER_REAL_IP,
            'user_agent_string' => HTTP_USER_AGENT
        ];

        $payment_id = PDO_DB::insert($payment_data, self::TABLE);

        foreach ($real_servises as $item) {
            $servicePostSum = str_replace(",", ".", $data[$item."_sum"]);

            $counter_data = [];
            if (!empty($data[$item.'_new_count'])) {
                foreach ($data[$item.'_new_count'] as $key => $counter) {
                    if (empty($data[$item.'_new_count'][$key])) {
                        continue;
                    }

                    $old_value = round(str_replace(',', '.', $data[$item.'_old_count'][$key]), 3);
                    $new_value = round(str_replace(',', '.', $data[$item.'_new_count'][$key]), 3);
                    $cur_value = round(str_replace(',', '.', $data[$item.'_cur_count'][$key]), 3);
                    $used_value = $new_value - $old_value;

                    if ($used_value < 0) {
                        $used_value -= pow(10, strlen(floor($old_value)));
                    }

                    $counter_data[] = [
                        'counter_num' => $data[$item.'_count_number'][$key],
                        'abcounter'   => $data[$item.'_abcounter'][$key],
                        'old_value'   => $old_value,
                        'new_value'   => $new_value,
                        'cur_value'   => $cur_value,
                        'pcount'      => $used_value,
                    ];
                }
            }

            $serviceDataTmp = explode("_", $data[$item."_data"]);

            $kombebt_data = [
                'id_firme'   => $serviceDataTmp[0],
                'kode_plat'  => $serviceDataTmp[1],
                'abcount'    => $serviceDataTmp[2],
                'platcode'   => $serviceDataTmp[3],
                'firme_bank' => $serviceDataTmp[4],
                'bank_code'  => $serviceDataTmp[5],
                'dbegin'     => $serviceDataTmp[6],
                'dend'       => $serviceDataTmp[7],
                'fio'        => $serviceDataTmp[8],
                'date_d'     => $data[$item.'_date_d'],
                'id_pat'     => $data[$item.'_id_pat'],
                'name_plat'  => $data[$item.'_name_plat'],
                'firm_name'  => $data[$item.'_firm_name'],
                'code_firme' => $data[$item.'_code_firme'],
            ];

            $serviceData = [
                'sum'          => $servicePostSum,
                'payment_id'   => $payment_id,
                'user_id'      => $user_id,
                'timestamp'    => $timestamp,
                'data'         => json_encode($kombebt_data, JSON_UNESCAPED_UNICODE),
                'counter_data' => json_encode($counter_data, JSON_UNESCAPED_UNICODE)
            ];

            $serviceId = PDO_DB::insert($serviceData, self::SERVICE_TABLE);
        }

        return $payment_id;
    }

    public static function getPDF($payment_id, $first = false)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || ($payment['status'] != 'success')) {
            return;
        }

        $pdf1_url = API_URL . self::PDF_NEW_CURRENT_KOM . $payment['reports_id_pack'];
        $pdf1 = Http::fgets($pdf1_url);

        if ($first) {
            return $pdf1;
        }

        $pdf2_url = API_URL . self::PDF_NEW_HISTORY_KOM . $payment['reports_id_plat_klient'];
        $pdf2 = Http::fgets($pdf2_url);

        return (strlen($pdf1) > strlen($pdf2)) ? $pdf1 : $pdf2;
    }

    public static function sendFirstPDF($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || $payment['send_first_pdf'] || ($payment['status'] != 'success')) {
            return;
        }

        $pdf = self::getPDF($payment['id'], true);
        $user = User::getUserById($payment['user_id']);

        if (!$pdf || !$user) {
            return;
        }

        $email = new Email();
        $email->addStringAttachment($pdf, "Receipt-{$payment['id']}.pdf");
        $success = $email->send(
            [$user['email'], "{$user['name']} {$user['fathername']}"],
            'Квитанція про сплату №' . $payment['id'],
            ''
        );

        if ($success) {
            PDO_DB::update(['send_first_pdf' => 1], self::TABLE, $payment['id']);
        }

        return $success;
    }

    public static function getPaymentXml($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if ($payment === null) {
            return '';
        }

        self::pppGetCashier($payment['processing'], $login, $password);

        $services = PDO_DB::table_list(self::SERVICE_TABLE, "payment_id='{$payment['id']}'", 'id ASC', $payment['count_services'] . '');

        $xml = '<?xml version="1.0" encoding="windows-1251" ?>';
        $xml .= "<rowset>";
        $xml .= "<login>{$login}</login>";
        $xml .= "<pwd>{$password}</pwd>";
        $xml .= "<summ_comis>". ($payment['summ_komis'] * 100) ."</summ_comis>";
        $xml .= "<idsiteuser>{$payment['user_id']}</idsiteuser>";
        $xml .= "<uniqid>". md5(uniqid(rand(), 1)) ."</uniqid>";
        
        $xml .= "<plat_list>";

        for ($i=0; $i < count($services); $i++) {
            $data = (array)json_decode($services[$i]['data']);
            $counters = (array)json_decode($services[$i]['counter_data']);
            $xml .= "<plat>";

            $xml .= "<plat_code>{$data['platcode']}</plat_code>";
            $xml .= "<abcount>{$data['abcount']}</abcount>";
            $xml .= "<id_firme>{$data['id_firme']}</id_firme>";
            $xml .= "<id_plat>{$data['id_pat']}</id_plat>";
            $xml .= "<summ_plat>". ($services[$i]['sum'] * 100) ."</summ_plat>";

            list($year, $month, $day) = explode('-', $data['dbegin']);
            $xml .= "<dbegin>$day.$month.$year 00:00:00</dbegin>";

            list($year, $month, $day) = explode('-', $data['dend']);
            $xml .= "<dend>$day.$month.$year 00:00:00</dend>";

            $xml .= "<counters>";
            for ($j=0; $j < count($counters); $j++) {

                $counters[$j] = (array)$counters[$j];
                $counters[$j]['old_value'] = str_replace('.', ',', $counters[$j]['old_value']);
                $counters[$j]['new_value'] = str_replace('.', ',', $counters[$j]['new_value']);
                $counters[$j]['cur_value'] = str_replace('.', ',', $counters[$j]['cur_value']);
                $counters[$j]['pcount'] = str_replace('.', ',', $counters[$j]['pcount']);

                $xml .= "<counter>";
                $xml .= "<abcounter>{$counters[$j]['abcounter']}</abcounter>";
                $xml .= "<counter_no>{$counters[$j]['counter_num']}</counter_no>";
                $xml .= "<old_value>{$counters[$j]['old_value']}</old_value>";
                $xml .= "<new_value>{$counters[$j]['new_value']}</new_value>";
                $xml .= "<cur_value>{$counters[$j]['cur_value']}</cur_value>";
                $xml .= "<pcount>{$counters[$j]['pcount']}</pcount>";
                $xml .= "</counter>";
            }
            $xml .= "</counters>";

            $xml .= "</plat>";
        }

        $xml .= "</plat_list>";
        $xml .= "</rowset>";
        $xml = str_replace('<counters></counters>', '', $xml);

        $xml = iconv('UTF-8', 'WINDOWS-1251', $xml);

        return $xml;
    }

    public static function check_payments_status($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || ($payment['status'] != 'new')) {
            return;
        }

        $payment['processing_data'] = (array)(@json_decode($payment['processing_data']));
        $to_update = [];

        PDO_DB::update($to_update, self::TABLE, $payment['id']);
    }

    public static function getErrorDescription($processing, $trancode)
    {
        return '';
    }

    public static function cron()
    {
        // проверяем статусы транзакций
        $time = time() - 300;
        $stm = $pdo->query("SELECT id FROM " . self::TABLE . " WHERE status='new' AND go_to_payment_time<$time AND go_to_payment_time IS NOT NULL");

        while ($row = $stm->fetch()) {
            self::check_payments_status($row['id']);
        }
    }
}
