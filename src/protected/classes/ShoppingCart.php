<?php

class ShoppingCart
{
    const TABLE = DB_TBL_PAYMENT;
    const SERVICE_TABLE = DB_TBL_PAYMENT_SERVICES;
    const KASS_ID_TAS  = '1080';
    const KASS_ID_AVAL = '1028';
    const KASS_ID_OSCHAD = '1085';//osc_site/osc_site123
    const KASS_ID_KHRESHCHATYK = '1048'; /// УЗНАТЬ ТОЧНЫЙ ID !!
    const REPORT_BASE_URL   = '/reports/rwservlet';
    const PDF_FIRST_URL     = '/reports/rwservlet?report=/home/oracle/reports/ppp/kv9_pack.rep&destype=cache&Desformat=pdf&cmdkey=rep&id_p=';
    const PDF_TODAY_URL     = '/reports/rwservlet?report=/home/oracle/reports/ppp/kvdbl9.rep&destype=Cache&Desformat=pdf&cmdkey=rep&id_k=';
    const PDF_NOT_TODAY_URL = '/reports/rwservlet?report=/home/oracle/reports/ppp/kvdbl9hist.rep&destype=Cache&Desformat=pdf&cmdkey=rep&id_k=';

    public static function getActivePaySystems($get_all_supported_paysystems = false)
    {
        return ($get_all_supported_paysystems)
            ? ['khreshchatyk', 'tas', '_test_upc', 'visa', 'mastercard', 'oschad']
            : ['khreshchatyk', 'tas', 'visa', 'mastercard', 'oschad'];
    }

    public static function getPercentRule($pay_system = null)
    {
        $rules = [
            '_test_upc'    => ['percent' => 2, 'min' => 2, 'big_after' => 1000, 'big_percent' => 3.5],
            'visa'         => ['percent' => 2, 'min' => 2],
            'tas'          => ['percent' => 2, 'min' => 2],
            'mastercard'   => ['percent' => 2, 'min' => 2],
            'khreshchatyk' => ['percent' => 0, 'min' => 0],
            'oschad'       => ['percent' => 0, 'min' => 0],
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

    public static function getKassID($processing)
    {
        switch ($processing) {
            case 'khreshchatyk':
                return self::KASS_ID_KHRESHCHATYK;

            case 'tas':
                return self::KASS_ID_TAS;

            case 'oschad':
                return self::KASS_ID_OSCHAD;

            default:
                return self::KASS_ID_AVAL;
        }
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
                foreach ($data[$item.'_new_count'] as $key => $counter) {
                    if (empty($data[$item.'_new_count'][$key])) {
                        continue;
                    }

                    $old_value = round(str_replace(',', '.', $data[$item.'_old_count'][$key]), 3);
                    $new_value = round(str_replace(',', '.', $data[$item.'_new_count'][$key]), 3);
                    $used_value = $new_value - $old_value;

                    if ($used_value < 0) {
                        $used_value -= pow(10, strlen(floor($old_value)));
                    }

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
                'date_d'     => $data[$item.'_date_d'],
                'id_pat'     => $data[$item.'_id_pat'],
                'name_plat'  => $data[$item.'_name_plat'],
                'firm_name'  => $data[$item.'_firm_name'],
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
            return Http::fgets(API_URL . self::PDF_FIRST_URL . $payment['reports_id_pack']);
        }

        $pdf1_url = API_URL . self::PDF_TODAY_URL . $payment['reports_id_plat_klient'] . '&num_group=' . $payment['reports_num_kvit'];
        $pdf2_url = API_URL . self::PDF_NOT_TODAY_URL . $payment['reports_id_plat_klient'] . '&num_group=' . $payment['reports_num_kvit'];

        $pdf1 = Http::fgets($pdf1_url);
        $pdf2 = Http::fgets($pdf2_url);

        return (strlen($pdf1) > strlen($pdf2)) ? $pdf1 : $pdf2;
    }

    public static function send_payment_status_to_reports($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || $payment['send_payment_status_to_reports'] || ($payment['status'] == 'new') || ($payment['status'] == 'timeout')) {
            return;
        }

        switch ($payment['type']) {
            case 'komdebt':

                switch ($payment['processing']) {

                    case '_test_upc':
                    case 'mastercard':
                    case 'visa':
                    case 'tas':
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

                        if ($payment['processing'] == 'tas') {
                            $paytime = DateTime::createFromFormat('d-m-Y H:i:s', $actual_upc_data['TIME']);
                            $paytime = ($paytime === false) ? microtime(true) : date_timestamp_get($paytime);

                            $post_data['p2']  = $payment['processing_data']['first']->termname;
                            $post_data['p3']  = rawurlencode($payment['summ_total'] * 100);
                            $post_data['p4']  = '980';
                            $post_data['p5']  = strftime("%y%m%d%H%M%S", $paytime);
                            $post_data['p6']  = $actual_upc_data['TRANID'];
                            $post_data['p9']  = $actual_upc_data['APPROVAL'];
                            $post_data['p11'] = $actual_upc_data['PAN'];
                            $post_data['p12'] = $actual_upc_data['RESPCODE'];
                            $post_data['p13'] = $actual_upc_data['SIGN'];
                        }
                        break;

                    case 'khreshchatyk':
                        $payment['processing_data'] = (array)(json_decode($payment['processing_data']));
                        $payment['processing_data']['dates'] = (array)$payment['processing_data']['dates'];
                        $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
                        $last = (array)$payment['processing_data']['requests'][count($payment['processing_data']['requests']) - 1];

                        $url = API_URL . self::REPORT_BASE_URL;

                        $post_data = [
                            'report'       => ($payment['status'] == 'success') ? 'prov_gkom.rep' : 'pacq50_gkom.rep',
                            'destype'      => 'Cache',
                            'Desformat'    => 'xml',
                            'cmdkey'       => 'rep',
                            'idplatklient' => $payment['reports_id_plat_klient'],
                            'p1'           => $last['MerchantID'],
                            'p2'           => $last['TerminalID'],
                            'p3'           => $last['TotalAmount'],
                            'p4'           => $last['Currency'],
                            'p5'           => '',
                            'p6'           => $last['OrderID'],
                            'p7'           => '',
                            'p8'           => '',
                            'p9'           => '',
                            'p10'          => $last['Rrn'],
                            'p11'          => $last['ProxyPan'],
                            'p12'          => $last['TranCode'],
                            'p13'          => '',
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

        // ERR = 7: Status of payment already sent
        if ($xml->ROW->ERR.'' && ($xml->ROW->ERR.'' != '7')) {
            if ($xml->ROW->ERR.'' == '4') {
                $to_update['send_payment_status_to_reports'] = 1;
            }

            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            // throw new Exception("id: {$payment['id']}, " . self::get_create_payment_error($xml->ROW->ERR.''));
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
            [$user['email'], "{$user['name']} {$user['fathername']}"],
            'Квитанція про сплату №' . $payment['id'],
            ''
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
                $xml .= "\t<id_kass>". self::getKassID($payment['processing']) ."</id_kass>\n";
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
                        $counters[$j]['old_value'] = str_replace('.', ',', $counters[$j]['old_value']);
                        $counters[$j]['new_value'] = str_replace('.', ',', $counters[$j]['new_value']);
                        $counters[$j]['pcount'] = str_replace('.', ',', $counters[$j]['pcount']);

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

        if ($xml === null || !isset($xml->ROW)) {
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

        // reports игнорирует комиссию, которую расчитал сайт. Но правильная комиссия его.
        // Они должны совпадать, но перезаменяем значение в БД на всякий случай

        $to_update['summ_komis']              = floatval($xml->ROW->SUMM_KOMIS.'') / 100;
        $to_update['summ_plat']               = floatval($xml->ROW->SUMM_PLAT.'')  / 100;
        $to_update['summ_total']              = floatval($xml->ROW->SUMM_TOTAL.'') / 100;

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

            case '1' : return 'Касовий день закритий';
            case '2' : return 'Стан фіксації (блокування) платежу. Вносити зміни не можна.';
            case '4' : return 'Немає платежу';
            case '5' : return 'Немає реквізиту';
            case '6' : return 'Сума платежа дорівнює 0';
            case '7' : return 'Платіж з таким id_plat_klient вже був проведений';
            case '8' : return 'Обов’язкові реквізити платежу не заповнені';
            case '9' : return 'Статус платежу не 20';
            case '10': return 'Помилка XML формату';

            case '100':
            default:
                return 'Невідома помилка ' . $error_code;
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

        switch ($payment['processing']) {
            case 'tas':
                $type = ($payment['type'] == 'komdebt') ? 'komdebt' : 'budget';
                $taslink = new TasLink($type);
                $taslink->checkStatus($payment['id']);
                break;

            case 'visa':
            case 'mastercard':

                $url = UPC::CHECK_STATUS_URL;
                $result = false;

                if (isset($payment['processing_data']['first']->upc_merchantid)) {
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

                    // Это было временно.
                    // if (!isset($payment['processing_data']['first']->upc_merchantid)) {
                    //     $payment['processing_data']['cron_check_status'] = [];
                    // }

                    if ($result && stristr($result, '403 Forbidden')) {
                        $result = false;
                    } else {
                        $payment['processing_data']['cron_check_status'][] = [
                            'timestamp' => microtime(true),
                            'raw_data' => $result,
                            'request' => $postdata
                        ];
                    }

                    $to_update['processing_data'] = json_encode($payment['processing_data']);
                }

                if (!$result) {
                    if (time() - $payment['timestamp'] >= 1800) {
                        $to_update['status'] = 'timeout';
                    }
                    break;
                }

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

                    // в дальнейшем эти данные будут использоваться для отправки статуса на reports
                    $date = date('d-m-Y H:i:s');

                    $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
                    $payment['processing_data']['dates'] = (array)$payment['processing_data']['dates'];
                    $payment['processing_data']['dates'][] = $date;
                    $payment['processing_data']['requests'][$date] = ['_is_from_cron_check_status' => true];

                    foreach ($params as $key => $value) {
                        $payment['processing_data']['requests'][$date][$key] = $value;
                    }
                    $to_update['processing_data'] = json_encode($payment['processing_data']);

                } elseif (in_array($params['TranCode'], ['105', '116', '111', '108', '101', '130', '290', '291', '401', '402', '403', '404', '405', '406', '407', '411', '412', '420', '421', '430', '431', '501', '502', '503', '504'])) {
                    $decline = true;
                } elseif (in_array($params['TranCode'], ['408', '409', '601'])) {
                    // логи в БД занимают слишком много места. Не логируем запросы статусов на транзакции, которые не завершены.
                    if (time() - $payment['go_to_payment_time'] >= 3600 * 3) {
                        $to_update['status'] = 'timeout';
                    } else {
                        unset($to_update);
                    }
                } else {
                    if (time() - $payment['go_to_payment_time'] >= 1800) {
                        $to_update['status'] = 'timeout';
                    }
                }

                if ($decline) {
                    $to_update['status'] = 'error';
                }
                break;

            default:
                return false;
        }


        if ($to_update) {
            if (isset($to_update['status']) && ($to_update['status'] == 'timeout')) {
                $to_update['send_payment_status_to_reports'] = 1;
            }
            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            self::send_payment_status_to_reports($payment['id']);
        }
    }

    public static function cron()
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->query("SELECT id FROM " . self::TABLE . " WHERE status<>'new' AND status<>'timeout' AND send_payment_status_to_reports=0 ORDER BY id ASC");

        while ($row = $stm->fetch()) {
            self::send_payment_status_to_reports($row['id']);
        }

        // проверяем статусы транзакций
        $time = time() - 300;
        $stm = $pdo->query("SELECT id FROM " . self::TABLE . " WHERE status='new' AND go_to_payment_time<$time AND go_to_payment_time IS NOT NULL");

        while ($row = $stm->fetch()) {
            self::check_payments_status($row['id']);
        }
    }
}
