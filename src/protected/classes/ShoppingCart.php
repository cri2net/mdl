<?php

class ShoppingCart
{
    const TABLE = DB_TBL_PAYMENT;
    const SERVICE_TABLE = DB_TBL_PAYMENT_SERVICES;
    const USE_TEST_KASS = true;
    const TEST_KASS_ID = '998';
    const REPORT_BASE_URL = '/reports/rwservlet';
    const PDF_FIRST_URL =     '/reports/rwservlet?report=kv9_pack.rep&destype=cache&Desformat=pdf&cmdkey=rep&id_p=';
    const PDF_TODAY_URL =     '/reports/rwservlet?report=kvdbl9.rep&destype=Cache&Desformat=pdf&cmdkey=rep&id_k=';
    const PDF_NOT_TODAY_URL = '/reports/rwservlet?report=kvdbl9hist.rep&destype=Cache&Desformat=pdf&cmdkey=rep&id_k=';

    public static function getActivePaySystems($get_all_supported_paysystems = false)
    {
        return ($get_all_supported_paysystems)
            ? ['_test_upc', 'aval', 'other', 'imeks', 'webmoney', 'visa', 'mastercard', 'private', 'mtb', 'w1']
            : ['_test_upc', 'aval', 'other', 'imeks', 'webmoney', 'visa', 'mastercard', 'private', 'mtb', 'w1'];
    }
    
    public static function get_id_kass()
    {
        if (self::USE_TEST_KASS) {
            return self::TEST_KASS_ID;
        }

        throw new Exception("SET WORK KASS ID");
    }

    public static function getPercentRule($pay_system = null)
    {
        $rules = [
            '_test_upc'  => ['percent' => 2, 'min' => 2, 'big_after' => 1000, 'big_percent' => 3.5],
            'aval'       => ['percent' => 2, 'min' => 2, 'big_after' => 1000, 'big_percent' => 3.5],
            'other'      => ['percent' => 2, 'min' => 2, 'big_after' => 1000, 'big_percent' => 3.5],
            'imeks'      => ['percent' => 2, 'min' => 2],
            'webmoney'   => ['percent' => 2, 'min' => 2],
            'visa'       => ['percent' => 2, 'min' => 2],
            'mastercard' => ['percent' => 2, 'min' => 2],
            'private'    => ['percent' => 2, 'min' => 2],
            'mtb'        => ['percent' => 2, 'min' => 2],
            'w1'         => ['percent' => 4, 'min' => 2],
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
            return sprintf('%.2f', max($sum * $rules[$type]['percent'] / 100, $rules[$type]['min']));
        }
        
        throw new Exception("UNKNOW TYPE $type");
    }
    
    public static function getTotalDebtSum($data)
    {
        $sum = 0;
        foreach($data['items'] as $item) {
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
        
        if (count($data['items']) > 0) {
            foreach($data['items'] as $item) {
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

        $timestamp = microtime(true);

        $payment_data = [
            'user_id' => $user_id,
            'acq' => '',
            'timestamp' => $timestamp,
            'type' => 'komdebt',
            'flat_id' => $data['flat_id'],
            'city_id' => $data['city_id'],
            'count_services' => count($real_servises),
            'reports_data' => '',
            'summ_plat' => $summ_plat,
            'summ_komis' => '',
            'summ_total' => '',
            'ip' => USER_REAL_IP,
            'user_agent_string' => HTTP_USER_AGENT
        ];

        $payment_id = PDO_DB::insert($payment_data, self::TABLE);
        
        foreach ($real_servises as $item) {
            $servicePostSum = str_replace(",", ".", $data[$item."_sum"]);
            
            $counter_data = [];
            if (!empty($data[$item.'_new_count'])) {
                foreach($data[$item.'_new_count'] as $key => $counter) {
                    if (empty($data[$item.'_new_count'][$key])) {
                        continue;
                    }

                    $old_value = $data[$item.'_old_count'][$key];
                    $new_value = $data[$item.'_new_count'][$key];
                    $used_value = $new_value - $old_value;

                    if ($used_value < 0)
                        $used_value -= pow(10, strlen(floor($old_value)));
                    
                    $counter_data[] = [
                        'counter_num' => $data[$item.'_count_number'][$key],
                        'abcounter'   => $data[$item.'_abcounter'][$key],
                        'old_value'   => $old_value,
                        'new_value'   => $new_value,
                        'pcount'      => $used_value,
                    ];
                }
            }

            $serviceDataTmp = explode("_", $data[$item."_data"]);

            $kombebt_data = [
                'kode_firme' => $serviceDataTmp[0],
                'kode_plat'  => $serviceDataTmp[1],
                'abcount'    => $serviceDataTmp[2],
                'platcode'   => $serviceDataTmp[3],
                'firme_bank' => $serviceDataTmp[4],
                'bank_code'  => $serviceDataTmp[5],
                'dbegin'     => $serviceDataTmp[6],
                'dend'       => $serviceDataTmp[7],
                'fio'        => $serviceDataTmp[8],
                'date_d'     => $data[$item."_date_d"],
                'id_pat'     => $data[$item."_id_pat"],
            ];
            
            $serviceData = [
                'sum'          => $servicePostSum,
                'payment_id'   => $payment_id,
                'user_id'      => $user_id,
                'timestamp'    => $timestamp,
                'data'         => json_encode($kombebt_data),
                'counter_data' => json_encode($counter_data)
            ];
            
            $serviceId = PDO_DB::insert($serviceData, self::SERVICE_TABLE);
        }
        
        return $payment_id;
    }

    public static function getPDF($payment_id, $first = false)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if ($payment === null) {
            return;
        }

        if ($first) {
            return Http::HttpGet(API_URL . self::PDF_FIRST_URL . $payment['reports_id_pack']);
        }
        
        $pdf1_url = API_URL . self::PDF_TODAY_URL . $payment['reports_id_plat_klient'] . '&num_group=' . $payment['reports_num_kvit'];
        $pdf2_url = API_URL . self::PDF_NOT_TODAY_URL . $payment['reports_id_plat_klient'] . '&num_group=' . $payment['reports_num_kvit'];

        $pdf1 = Http::HttpGet($pdf1_url);
        $pdf2 = Http::HttpGet($pdf2_url);

        return (strlen($pdf1) > strlen($pdf2)) ? $pdf1 : $pdf2;
    }

    public static function send_payment_status_to_reports($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || $payment['send_payment_status_to_reports'] || ($payment['status'] == 'new')) {
            return;
        }

        switch ($payment['type']) {
            case 'komdebt':

                switch ($payment['processing']) {
                    case '_test_upc':
                        $payment['processing_data'] = (array)(json_decode($payment['processing_data']));
                        $payment['processing_data']['dates'] = (array)$payment['processing_data']['dates'];
                        $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
                        $actual_date = $payment['processing_data']['dates'][count($payment['processing_data']['dates']) - 1];
                        $actual_upc_data = (array)$payment['processing_data']['requests'][$actual_date];

                        $url = API_URL . self::REPORT_BASE_URL;
                        
                        $post_data = [
                            'report'       => ($payment['status'] == 'success') ? 'prov_gkom.rep' : 'pacq50_gkom.rep',
                            'destype'      => 'Cache',
                            'Desformat'    => 'xml',
                            'cmdkey'       => 'rep',
                            'idplatklient' => $payment['reports_id_plat_klient'],
                            'p1'           => $actual_upc_data['MerchantID'],
                            'p2'           => $actual_upc_data['TerminalID'],
                            'p3'           => $actual_upc_data['TotalAmount'],
                            'p4'           => $actual_upc_data['Currency'],
                            'p5'           => $actual_upc_data['PurchaseTime'],
                            'p6'           => $actual_upc_data['OrderID'],
                            'p7'           => $actual_upc_data['XID'],
                            'p8'           => $actual_upc_data['SD'],
                            'p9'           => $actual_upc_data['ApprovalCode'],
                            'p10'          => $actual_upc_data['Rrn'],
                            'p11'          => $actual_upc_data['ProxyPan'],
                            'p12'          => $actual_upc_data['TranCode'],
                            'p13'          => $actual_upc_data['Signature'],
                            'p14'          => 0,  // delay
                        ];

                        break;

                    default:
                        throw new Exception("Unknow payment processing in send_payment_status_to_reports()");
                        return false;
                }
                break;


            default:
                throw new Exception("Unknow payment type in send_payment_status_to_reports()");
                return false;
        }

        $res = Http::HttpPost($url, $post_data);

        $date = date('Y-m-d H:i:s');
        $xml_string = iconv('CP1251', 'UTF-8', $res);
        $reports_data = (array)(@json_decode($payment['reports_data']));
        if (!$reports_data) {
            $reports_data = [];
        }
        
        $reports_data[$date] = array(
            'timestamp' => microtime(true),
            'reports_url' => $url,
            'send_data' => $post_data,
            'answer' => $xml_string,
        );
        $to_update = ['reports_data' => json_encode($reports_data)];

        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = simplexml_load_string($xml_string);

        if ($xml === null) {
            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            throw new Exception(ERROR_CREATE_PAYMENT_XML);
            return false;
        }

        // ERR = 7: Status of payment already sent
        if ($xml->ROW->ERR.'' && ($xml->ROW->ERR.'' != '7')) {
            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            throw new Exception(self::get_create_payment_error($xml->ROW->ERR.''));
            return false;
        }

        $to_update['reports_num_kvit']               = $xml->ROW->NUM_KVIT.'';
        $to_update['acq']                            = ($xml->ROW->ACQ.'')            ? $xml->ROW->ACQ.''            : $payment['acq'];
        $to_update['reports_id_pack']                = ($xml->ROW->ID_PACK.'')        ? $xml->ROW->ID_PACK.''        : $payment['reports_id_pack'];
        $to_update['reports_id_plat_klient']         = ($xml->ROW->ID_PLAT_KLIENT.'') ? $xml->ROW->ID_PLAT_KLIENT.'' : $payment['reports_id_plat_klient'];
        $to_update['send_payment_status_to_reports'] = 1;

        PDO_DB::update($to_update, self::TABLE, $payment['id']);
        self::sendFirstPDF($payment['id']);
        return true;
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
            array($user['email'], "{$user['name']} {$user['fathername']}"),
            'Квитанція про сплату'
        );

        if ($success) {
            PDO_DB::update(['send_first_pdf' => 1], self::TABLE, $payment['id']);
        }

        return $success;
    }

    public static function send_payment_to_reports($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || $payment['send_payment_to_reports']) {
            return;
        }

        $services = PDO_DB::table_list(self::SERVICE_TABLE, "payment_id='{$payment['id']}'", 'id ASC', $payment['count_services'] . '');

        switch ($payment['type']) {
            case 'komdebt':
                $xml = '<?xml version="1.0" encoding="windows-1251" ?>';
                $xml .= "\n<rowset>\n";
                $xml .= "\t<id_kass>". self::get_id_kass() ."</id_kass>\n";
                $xml .= "\t<summ_comis>". ($payment['summ_komis'] * 100) ."</summ_comis>\n";
                $xml .= "\t<idsiteuser>{$payment['user_id']}</idsiteuser>\n";

                $xml .= "\t<plat_list>\n";
                
                for ($i=0; $i < count($services); $i++) {
                    $data = (array)json_decode($services[$i]['data']);
                    $counters = (array)json_decode($services[$i]['counter_data']);
                    $xml .= "\t\t<plat>\n";

                    $xml .= "\t\t\t<plat_code>{$data['platcode']}</plat_code>\n";
                    $xml .= "\t\t\t<abcount>{$data['abcount']}</abcount>\n";
                    $xml .= "\t\t\t<id_firme>{$data['kode_firme']}</id_firme>\n";
                    $xml .= "\t\t\t<id_plat>{$data['id_pat']}</id_plat>\n";
                    $xml .= "\t\t\t<date_d>{$data['date_d']}</date_d>\n";
                    $xml .= "\t\t\t<summ_plat>". ($services[$i]['sum'] * 100) ."</summ_plat>\n";
                    
                    list($year, $month, $day) = explode('-', $data['dbegin']);
                    $xml .= "\t\t\t<dbegin>$day.$month.$year 00:00:00</dbegin>\n";
                    
                    list($year, $month, $day) = explode('-', $data['dend']);
                    $xml .= "\t\t\t<dend>$day.$month.$year 00:00:00</dend>\n";

                    
                    $xml .= "\t\t\t<counters>\n";
                    for ($j=0; $j < count($counters); $j++) {
                        $counters[$j] = (array)$counters[$j];
                        $xml .= "\t\t\t\t<counter>\n";
                        $xml .= "\t\t\t\t\t<abcounter>{$counters[$j]['abcounter']}</abcounter>\n";
                        $xml .= "\t\t\t\t\t<counter_no>{$counters[$j]['counter_num']}</counter_no>\n";
                        $xml .= "\t\t\t\t\t<old_value>{$counters[$j]['old_value']}</old_value>\n";
                        $xml .= "\t\t\t\t\t<new_value>{$counters[$j]['new_value']}</new_value>\n";
                        $xml .= "\t\t\t\t\t<pcount>{$counters[$j]['pcount']}</pcount>\n";
                        $xml .= "\t\t\t\t</counter>\n";
                    }
                    $xml .= "\t\t\t</counters>\n";
                    
                    $xml .= "\t\t</plat>\n";
                }

                $xml .= "\t</plat_list>\n";
                $xml .= "</rowset>";
                break;
        }

        $xml = iconv('UTF-8', 'WINDOWS-1251', $xml);
        $url = API_URL . self::REPORT_BASE_URL;
        $post_data = [
            'report' => 'pnew_gkom.rep',
            'destype' => 'Cache',
            'Desformat' => 'xml',
            'cmdkey' => 'rep',
            'in_xml' => $xml
        ];
        $res = Http::HttpPost($url, $post_data);

        $date = date('Y-m-d H:i:s');
        $xml_string = iconv('CP1251', 'UTF-8', $res);
        $reports_data = (array)(@json_decode($payment['reports_data']));
        if (!$reports_data) {
            $reports_data = [];
        }
        
        $reports_data[$date] = [
            'timestamp' => microtime(true),
            'reports_url' => $url,
            'send_data' => $post_data,
            'answer' => $xml_string,
        ];
        $to_update = ['reports_data' => json_encode($reports_data)];

        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = simplexml_load_string($xml_string);

        if ($xml === null) {
            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            throw new Exception(ERROR_CREATE_PAYMENT_XML);
            return false;
        }


        if ($xml->ROW->ERR.'') {
            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            throw new Exception(self::get_create_payment_error($xml->ROW->ERR.''));
            return false;
        }

        $to_update['acq']                     = $xml->ROW->ACQ.'';
        $to_update['reports_id_pack']         = $xml->ROW->ID_PACK.'';
        $to_update['reports_num_kvit']        = $xml->ROW->NUM_KVIT.'';
        $to_update['reports_id_plat_klient']  = $xml->ROW->ID_PLAT_KLIENT.'';
        $to_update['send_payment_to_reports'] = 1;

        PDO_DB::update($to_update, self::TABLE, $payment['id']);
        return true;
    }

    public static function get_create_payment_error($error_code)
    {
        $error_code = trim($error_code);

        switch ($error_code) {
            case '':
            case '0':
                return '';
            
            case '1' : return 'День закрыт';
            case '2' : return 'Cостояние фиксации (блокирования) платежа. Вносить изменения нельзя.';
            case '4' : return 'Нет платежа';
            case '5' : return 'Нет реквизита';
            case '6' : return 'Сумма платежа равна 0';
            case '7' : return 'Платеж с таким id_plat_klient уже был проведен';
            case '8' : return 'Обязательные реквизиты платежа не заполнены';
            case '9' : return 'Статус платежа не 20';
            case '10': return 'Ошибка XML формата';
            
            case '100':
            default:
                return 'Неизвестная ошибка ' . $error_code;
        }
    }

    public static function check_payments_status($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || ($payment['status'] != 'new')) {
            return;
        }

        $payment['processing_data'] = (array)(@json_decode($payment['processing_data']));
        $to_update = [];

        switch($payment['processing']) {
            case '_test_upc':
                // по идее на тестовом мерчанте это не работает. Код для всех остальных UPC мерчантов

                $url = UPC::ACTION;
                
                $postdata = [
                    'MerchantID'   => $payment['processing_data']['first']->upc_merchantid,
                    'TerminalID'   => $payment['processing_data']['first']->upc_terminalid,
                    'OrderID'      => $payment['processing_data']['first']->upc_orderid,
                    'Currency'     => $payment['processing_data']['first']->upc_currency,
                    'TotalAmount'  => $payment['processing_data']['first']->upc_totalamount,
                    'PurchaseTime' => $payment['processing_data']['first']->upc_purchasetime
                ];

                $result = Http::HttpPost($url, $postdata);

                if (!isset($payment['processing_data']['cron_check_status'])) {
                    $payment['processing_data']['cron_check_status'] = [];
                }
                
                $payment['processing_data']['cron_check_status'][] = [
                    'timestamp' => microtime(true),
                    'raw_data' => $result
                ];
                
                $to_update['processing_data'] = json_encode($payment['processing_data']);


                if (!$result) {
                    $decline = (time() - $payment['timestamp'] >= 1800);
                } else {
                    $lines = explode("\n", $result);
                    $params = [];
                    
                    for ($i=0; $i < count($lines); $i++) {
                        $vars = explode('=', $lines[$i]);

                        $var = trim($vars[0]);
                        if (strlen($var) > 0) {
                            $params[$var] = trim($vars[1]);
                        }
                    }

                    if (in_array($params['TranCode'], ['000', '410'])) {
                        $to_update['status'] = 'success';
                    } elseif (in_array($params['TranCode'], ['105', '116', '111', '108', '101', '130', '290', '291', '401', '402', '403', '404', '405', '406', '407', '411', '412', '420', '421', '430', '431', '501', '502', '503', '504'])) {
                        $decline = true;
                    } elseif (in_array($params['TranCode'], ['408', '409'])) {
                        $decline = (time() - $payment['timestamp'] >= 900);
                    }

                    if ($decline) {
                        $to_update['status'] = 'error';
                    }
                }
                break;

            default:
                return false;
        }


        if ($to_update) {
            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            self::send_payment_status_to_reports($payment['id']);
        }
    }

    public static function get_payments_to_check_status()
    {
        $time = time() - 300;
        return PDO_DB::table_list(self::TABLE, "status='new' AND `timestamp`<$time", "id ASC");
    }

    public static function cron()
    {
        $arr = PDO_DB::table_list(self::TABLE, "`status`<>'new' AND send_payment_status_to_reports=0", "id ASC");
        
        for ($i=0; $i < count($arr); $i++) {
            self::send_payment_status_to_reports($arr[$i]['id']);
        }


        // проверяем статусы транзакций
        $arr = self::get_payments_to_check_status();
        for ($i=0; $i < count($arr); $i++) {
            self::check_payments_status($arr[$i]['id']);
        }
    }
}
