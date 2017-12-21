<?php

use cri2net\php_pdo_db\PDO_DB;

class ShoppingCart
{
    const TABLE             = DB_TBL_PAYMENT;
    const SERVICE_TABLE     = DB_TBL_PAYMENT_SERVICES;
    const REPORT_BASE_URL   = '/reports/rwservlet';
    const PDF_TODAY_URL     = '/reports/rwservlet?report=/ppp/kv_www_all.rep&destype=Cache&Desformat=pdf&cmdkey=rep&id_p=';
    const PDF_NOT_TODAY_URL = '/reports/rwservlet?report=/ppp/kv_www_hist.rep&destype=Cache&Desformat=pdf&cmdkey=rep&id_k=';

    public static function getActivePaySystems($get_all_supported_paysystems = false)
    {
        return ($get_all_supported_paysystems)
            ? ['tas', 'visa', 'mastercard', 'oschad', 'oschad_mycard', 'oschadbank', 'webmoney']
            : ['tas', 'visa', 'mastercard', 'oschad', 'oschad_mycard', 'oschadbank', 'webmoney'];
    }

    public static function get_API_URL($key)
    {
        $urls = [];

        $urls['KASS_STATUS'] = '/reports/rwservlet?report=/gerc_api/api_status_kass.rep&cmdkey=rep&destype=cache&Desformat=xml&login=';
        $urls['KASS_OPEN']   = '/reports/rwservlet?report=/gerc_api/api_open_kass.rep&cmdkey=rep&destype=cache&Desformat=xml&login=';

        return $urls[$key];
    }

    /**
     * Получаем логин и хеш пароля кассира
     * @param  string $processing Ключ процессинга
     * @param  string $login      Логин кассира (SHA1, кажется)
     * @param  string $password   Хеш пароля кассира
     * 
     * @return void
     */
    public static function pppGetCashierByProcessing($processing, &$login, &$password)
    {
        switch ($processing) {
            case 'visa':
            case 'mastercard':
                $login    = 'CKS_SITE_COM_RB';
                $password = '592F95260CBC0CAC0A5A71A4C3753497AD32D310';
                break;

            case 'tas':
                $login    = 'cks_site_com';
                $password = '0168F38DC7806216FB81C0CEAEF044713A1397BE';
                break;

            case 'webmoney':
                $login    = 'cks_site_com_wm';
                $password = strtoupper(sha1('cks_site_com_wm123'));
                break;

            case 'oschad_mycard':
                $login    = 'CKS_SITE_COM_OB';
                $password = strtoupper(sha1('cks_site_com_ob123'));
                break;

            case 'oschadbank':
                $login    = 'cks_site_com_ob2';
                $password = strtoupper(sha1('cks_site_com_ob2123'));
                break;

            case 'oschad':
                $login    = 'cks_site_com_ob3';
                $password = strtoupper(sha1('cks_site_com_ob3123'));
                break;

            case 'masterpass':
                $login    = 'MASTERPASS_CKSCOMUA';
                $password = 'CC8FE019D808CE541C40518049042AFB069BCA98';
                break;
        }
    }

    /**
     * Метод проверяет открыта ли касса в оракле
     * @param  string $processing
     * @return boolean
     */
    public static function pppCheckOpenKass($processing)
    {
        self::pppGetCashierByProcessing($processing, $login, $password);

        $url = API_URL . self::get_API_URL('KASS_STATUS');
        $url .= rawurlencode($login);
        $url .= '&pwd=' . rawurlencode($password);

        $xml_string = Http::fgets($url);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = simplexml_load_string($xml_string);

        if (($xml === null) || ($xml === false)) {
            return false;
        }

        $row = $xml->ROW;

        if ($row->ERR.'' && ($row->ERR.'' != '0')) {
            throw new Exception("Ошибка при выполнении запроса $row->ERR");
            return;
        }

        $status = $row->STATUS_KASS.'';
        return ($status == '1');
    }

    /**
     * Метод пробует открыть кассу в оракле
     * @param  integer $processing
     * @param  integer $shift   Смена кассы: 0 = дневная, 1 = вечерняя. OPTIONAL
     * @return void
     */
    public static function pppOpenKass($processing, $shift = 0)
    {
        self::pppGetCashierByProcessing($processing, $login, $password);

        $url = API_URL . self::get_API_URL('KASS_OPEN');
        $url .= rawurlencode($login);
        $url .= '&pwd=' . rawurlencode($password);
        $url .= '&smena=' . $shift;

        $xml_string = Http::fgets($url);
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
            'visa'          => ['percent' => 2, 'min' => 2],
            'tas'           => ['percent' => 2, 'min' => 2],
            'mastercard'    => ['percent' => 2, 'min' => 2],
            'oschad'        => ['percent' => 0, 'min' => 0],
            'oschad_mycard' => ['percent' => 0, 'min' => 0],
            'oschadbank'    => ['percent' => 2, 'min' => 2],
            'webmoney'      => ['percent' => 2, 'min' => 2],
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

        $pdf1_url = API_URL . self::PDF_TODAY_URL     . $payment['reports_id_pack'];
        $pdf1 = Http::fgets($pdf1_url);

        if ($first) {
            return $pdf1;
        }

        $pdf2_url = API_URL . self::PDF_NOT_TODAY_URL . $payment['reports_id_plat_klient'];
        $pdf2 = Http::fgets($pdf2_url);

        return (strlen($pdf1) > strlen($pdf2)) ? $pdf1 : $pdf2;
    }

    public static function send_payment_status_to_reports($payment_id)
    {
        $payment = PDO_DB::row_by_id(self::TABLE, $payment_id);
        if (($payment === null) || $payment['send_payment_status_to_reports'] || ($payment['status'] == 'new') || ($payment['status'] == 'timeout')) {
            return;
        }

        $url = API_URL . self::REPORT_BASE_URL;
        $to_update = [];

        self::pppGetCashierByProcessing($payment['processing'], $login, $password);

        switch ($payment['type']) {
            case 'gai':
            case 'kinders':
            case 'direct':
            case 'budget':
                $report = ($payment['status'] == 'success') ? '/gerc_api/api_prov.rep' : '/gerc_api/api_pacq50.rep';
                break;

            case 'cks':
            case 'komdebt':
                $report = ($payment['status'] == 'success') ? '/gerc_api/prov_gkom.rep' : '/gerc_api/pacq50_gkom.rep';
                break;
        }

        switch ($payment['processing']) {

            case '_test_upc':
            case 'mastercard':
            case 'visa':
            case 'tas':
            case 'webmoney':
                $payment['processing_data'] = (array)(json_decode($payment['processing_data']));
                $payment['processing_data']['dates'] = (array)$payment['processing_data']['dates'];
                $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
                $actual_date = $payment['processing_data']['dates'][count($payment['processing_data']['dates']) - 1];
                $actual_upc_data = (array)$payment['processing_data']['requests'][$actual_date];

                $url .= '?report='       . rawurlencode($report);
                $url .= '&destype='      . rawurlencode('Cache');
                $url .= '&Desformat='    . rawurlencode('xml');
                $url .= '&cmdkey='       . rawurlencode('rep');

                $url .= '&idplatklient=' . rawurlencode($payment['reports_id_plat_klient']);
                $url .= '&p1='           . rawurlencode($actual_upc_data['MerchantID']);

                if ($payment['processing'] == 'tas') {
                    $paytime = DateTime::createFromFormat('d-m-Y H:i:s', $actual_upc_data['TIME']);
                    $paytime = ($paytime === false) ? microtime(true) : date_timestamp_get($paytime);

                   $url .= '&p2='        . rawurlencode($payment['processing_data']['first']->termname);
                   $url .= '&p3='        . rawurlencode($payment['summ_total'] * 100);
                   $url .= '&p4='        . rawurlencode('980');
                   $url .= '&p5='        . rawurlencode(strftime("%y%m%d%H%M%S", $paytime));
                   $url .= '&p6='        . rawurlencode($actual_upc_data['TRANID']);
                } else {
                    $url .= '&p2='       . rawurlencode($actual_upc_data['TerminalID']);
                    $url .= '&p3='       . rawurlencode($actual_upc_data['TotalAmount']);
                    $url .= '&p4='       . rawurlencode($actual_upc_data['Currency']);
                    $url .= '&p5='       . rawurlencode($actual_upc_data['PurchaseTime']);
                    $url .= '&p6='       . rawurlencode($actual_upc_data['OrderID']);
                }

                $url .= '&p7='           . rawurlencode($actual_upc_data['XID']);
                $url .= '&p8='           . rawurlencode($actual_upc_data['SD']);

                if ($payment['processing'] == 'tas') {
                   $url .= '&p9='        . rawurlencode($actual_upc_data['APPROVAL']);
                } else {
                    $url .= '&p9='       . rawurlencode($actual_upc_data['ApprovalCode']);
                }

                $url .= '&p10='          . rawurlencode($actual_upc_data['Rrn']);

                if ($payment['processing'] == 'tas') {
                    $url .= '&p11='       . rawurlencode($actual_upc_data['PAN']);
                    $url .= '&p12='       . rawurlencode($actual_upc_data['RESPCODE']);
                    $url .= '&p13='       . rawurlencode($actual_upc_data['SIGN']);
                    $to_update['trancode'] = $actual_upc_data['RESPCODE'];
                } else {
                    $url .= '&p11='      . rawurlencode($actual_upc_data['ProxyPan']);
                    $url .= '&p12='      . rawurlencode($actual_upc_data['TranCode']);
                    $url .= '&p13='      . rawurlencode($actual_upc_data['Signature']);
                    $to_update['trancode'] = $actual_upc_data['TranCode'];
                }

                $url .= '&p14=0';
                break;

            case 'oschad':
            case 'oschad_mycard':
            case 'oschadbank':
                $payment['processing_data'] = (array)(json_decode($payment['processing_data']));
                $payment['processing_data']['dates'] = (array)$payment['processing_data']['dates'];
                $payment['processing_data']['requests'] = (array)$payment['processing_data']['requests'];
                $actual_date = $payment['processing_data']['dates'][count($payment['processing_data']['dates']) - 1];
                $actual_osc_data = (array)$payment['processing_data']['requests'][$actual_date];
                $osc_first = $payment['processing_data']['first'];
                $url = API_URL . self::REPORT_BASE_URL;

                $url .= '?report='       . rawurlencode($report);
                $url .= '&destype='      . rawurlencode('Cache');
                $url .= '&Desformat='    . rawurlencode('xml');
                $url .= '&cmdkey='       . rawurlencode('rep');
                $url .= '&idplatklient=' . rawurlencode($payment['reports_id_plat_klient']);
                $url .= '&p1='           . rawurlencode($osc_first->MERCHANT);
                $url .= '&p2='           . rawurlencode($osc_first->TERMINAL);
                $url .= '&p3='           . rawurlencode($actual_osc_data['Amount']*100);
                $url .= '&p4='           . rawurlencode('980');
                $url .= '&p5='           . rawurlencode($osc_first->TIMESTAMP);
                $url .= '&p6='           . rawurlencode($actual_osc_data['Order']);
                $url .= '&p7=';
                $url .= '&p8=';
                $url .= '&p9='           . rawurlencode($actual_osc_data['AuthCode']);
                $url .= '&p10='          . rawurlencode($actual_osc_data['RRN']);
                $url .= '&p11=';
                $url .= '&p12='          . rawurlencode($actual_osc_data['RC']);
                $url .= '&p13='          . rawurlencode($osc_first->P_SIGN);
                $url .= '&p14=0';
                $to_update['trancode'] = $actual_osc_data['RC'];
                break;

            default:
                throw new Exception("Unknow payment processing in send_payment_status_to_reports()");
                return false;
        }

        $url .= '&login=' . $login;
        $url .= '&pwd=' . $password;

        $res = Http::fgets($url);

        $date = date('Y-m-d H:i:s');
        $xml_string = iconv('CP1251', 'UTF-8', $res);

        $message_to_log = var_export(
            [
                'date' => date('Y-m-d H:i:s'),
                'timestamp' => microtime(true),
                'reports_url' => $url,
                'answer' => $xml_string,
            ],
            true
        );

        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = simplexml_load_string($xml_string);

        if (($xml === null) || ($xml === false)) {
            self::logRequestToReports($message_to_log, $payment['id'], false, 'status');
            return false;
        }

        $row_elem = $xml->ROW;
        
        if (isset($xml->ROWSET->ROW)) {
            $row_elem = $xml->ROWSET->ROW;
        }

        // ERR = 7: Status of payment already sent
        // ERR = 9: Status of payment not 20 (NEW)
        if ($row_elem->ERR.'' && ($row_elem->ERR.'' != '7') && ($row_elem->ERR.'' != '9')) {
            self::logRequestToReports($message_to_log, $payment['id'], false, 'status');
            if ($row_elem->ERR.'' == '4') {
                $to_update['send_payment_status_to_reports'] = 1;
            }

            PDO_DB::update($to_update, self::TABLE, $payment['id']);
            // throw new Exception("id: {$payment['id']}, " . self::get_create_payment_error($row_elem->ERR.''));
            return false;
        }

        $to_update['acq']                            = ($row_elem->ACQ.'')            ? $row_elem->ACQ.''            : $payment['acq'];
        $to_update['reports_id_pack']                = ($row_elem->ID_PACK.'')        ? $row_elem->ID_PACK.''        : $payment['reports_id_pack'];
        $to_update['reports_id_plat_klient']         = ($row_elem->ID_PLAT_KLIENT.'') ? $row_elem->ID_PLAT_KLIENT.'' : $payment['reports_id_plat_klient'];
        $to_update['send_payment_status_to_reports'] = 1;

        PDO_DB::update($to_update, self::TABLE, $payment['id']);
        self::logRequestToReports($message_to_log, $payment['id'], true, 'status');
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
        if ($payment === null) {
            return;
        }

        self::pppGetCashierByProcessing($payment['processing'], $login, $password);

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

        $xml = iconv('UTF-8', 'WINDOWS-1251', $xml);
        $report = '/gerc_api/pnew_gkom.rep';
        $url = API_URL . self::REPORT_BASE_URL . '?report=' . rawurlencode($report) . '&destype=Cache&Desformat=xml&cmdkey=rep';
        $url .= '&in_xml=' . rawurlencode($xml);
        $res = Http::fgets($url);

        $date = date('Y-m-d H:i:s');
        $xml_string = iconv('CP1251', 'UTF-8', $res);
        $to_update = [];

        $message_to_log = var_export(
            [
                'date' => date('Y-m-d H:i:s'),
                'timestamp' => microtime(true),
                'reports_url' => $url,
                'answer' => $xml_string,
            ],
            true
        );


        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = simplexml_load_string($xml_string);

        if (($xml === null) || ($xml === false) || !isset($xml->ROW)) {
            self::logRequestToReports($message_to_log, $payment['id'], false);
            throw new Exception(ERROR_CREATE_PAYMENT_XML);
            return false;
        }

        if ($xml->ROW->ERR.'') {
            self::logRequestToReports($message_to_log, $payment['id'], false);
            throw new Exception(self::get_create_payment_error($xml->ROW->ERR.''));
            return false;
        }

        self::logRequestToReports($message_to_log, $payment['id']);

        $to_update['acq']                     = $xml->ROW->ACQ.'';

        // reports игнорирует комиссию, которую расчитал сайт. Но правильная комиссия его.
        // Они должны совпадать, но перезаменяем значение в БД на всякий случай

        $to_update['summ_komis']              = floatval($xml->ROW->SUMM_KOMIS.'') / 100;
        $to_update['summ_plat']               = floatval($xml->ROW->SUMM_PLAT.'')  / 100;
        $to_update['summ_total']              = floatval($xml->ROW->SUMM_TOTAL.'') / 100;

        $to_update['reports_id_pack']         = $xml->ROW->ID_PACK.'';
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

            case '':
            case 'masterpass':
            case 'oschad':
            case 'oschad_mycard':
            case 'oschadbank':
                if (microtime(true) - $payment['go_to_payment_time'] > 3600) {
                    $to_update['status'] = 'timeout';
                }
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

                    if ($result && stristr($result, '403 Forbidden')) {
                        $result = false;
                    } else {
                        $payment['processing_data']['cron_check_status'][] = [
                            'timestamp' => microtime(true),
                            'raw_data' => $result,
                            'request' => $postdata
                        ];
                    }

                    $to_update['processing_data'] = json_encode($payment['processing_data'], JSON_UNESCAPED_UNICODE);
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
                    $to_update['processing_data'] = json_encode($payment['processing_data'], JSON_UNESCAPED_UNICODE);

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

    public static function getErrorDescription($processing, $trancode)
    {
        switch ($processing) {
            case '_test_upc':
            case 'mastercard':
            case 'visa':
                return UPC::get_upc_error($trancode);
                break;

            case 'tas':
                return TasLink::getErrorDescription($trancode);
                break;

            case 'oschad_mycard':
            case 'oschadbank':
                return Oschad::getErrorDescription($trancode);
        }

        return '';
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
