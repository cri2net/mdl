<?php

use cri2net\php_pdo_db\PDO_DB;

class Kinders
{
    public static function get_API_URL($key)
    {
        $urls = [];

        $urls['PPP_URL_INSTITUTION'] = '/reports/rwservlet?report=site_api/dic_rono_sad.rep&cmdkey=api_test&destype=cache&Desformat=xml&id_firme=';
        $urls['PPP_URL_CLASSES']     = '/reports/rwservlet?report=site_api/dic_rono_group&cmdkey=api_test&destype=cache&Desformat=xml&id_sad=';
        $urls['PPP_URL_CHILDREN']    = '/reports/rwservlet?report=site_api/dic_rono_child&cmdkey=api_test&destype=cache&Desformat=xml&id_rono_group=';
        $urls['PPP_URL_DEBT']        = '/reports/rwservlet?report=gerc_api/get_rono_debt&cmdkey=api_test&destype=cache&Desformat=xml&login=';
        $urls['PPP_URL_CITY']        = '/reports/rwservlet?report=site_api/dic_rono_sity.rep&cmdkey=api_test&destype=cache&Desformat=xml';
        $urls['PPP_URL_FIRME']       = '/reports/rwservlet?report=site_api/dic_rono_firme.rep&cmdkey=api_test&destype=cache&Desformat=xml&id_sity=';
        $urls['PPP_URL_CREATE']      = '/reports/rwservlet?report=gerc_api/api_pnew_rono.rep&cmdkey=api_test&destype=cache&Desformat=xml&login=';

        return $urls[$key];
    }

    /**
     * Получаем список городов
     * @return array
     */
    public static function getCitiesList()
    {
        $url = API_URL . self::get_API_URL('PPP_URL_CITY');
        try {
            $xml = Http::getXmlByUrl($url);
        } catch (Exception $e) {
            return ['list' => []];
        }

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $list[] = [
                'name_ru' => $row_elem[$i]->NAME_SITY_RU . '',
                'name_ua' => $row_elem[$i]->NAME_SITY_UKR . '',
                'id'      => $row_elem[$i]->ID_SITY . '',
            ];
        }

        return ['list' => $list];
    }

    /**
     * Список РОАМ (типа районов) города
     * @param  integer $city_id ID города
     * @return array
     */
    public static function getDistrictList($city_id)
    {
        $url = API_URL . self::get_API_URL('PPP_URL_FIRME') . $city_id;
        try {
            $xml = Http::getXmlByUrl($url);
        } catch (Exception $e) {
            return ['list' => []];
        }

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $list[] = [
                'name' => $row_elem[$i]->NAME_FIRME . '',
                'id'   => $row_elem[$i]->ID_FIRME . ''
            ];
        }

        return ['list' => $list];
    }

    /**
     * Получение списка районов (вроде)
     * @param  integer $id_area 233 = Киев
     * @return array
     */
    public static function getFirmeList($id_area = 233)
    {
        $url = API_URL . self::get_API_URL('PPP_URL_FIRME') . $id_area;
        $xml = Http::getXmlByUrl($url);

        $result = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $result[] = [
                'name' => $row_elem[$i]->NAME_FIRME . '',
                'id'   => $row_elem[$i]->ID_FIRME . '',
            ];
        }

        return $result;
    }

    public static function getDebt($child_id, &$debt_date = null)
    {
        ShoppingCart::pppGetCashierByKassId(ShoppingCart::KASS_ID_TAS, $login, $password);

        $url = API_URL . self::get_API_URL('PPP_URL_DEBT') . $login . '&pwd=' . $password . '&id_rono_child=' . $child_id;
        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);
        
        if ($xml === false || ($xml->ROW->ERR.'' != '0')) {
            return 0;
        }

        $debt = (double)($xml->ROW->SUMM_PLAT . '' / 100);
        $date = DateTime::createFromFormat('YmdHis', $xml->ROW->DATE_DEBT . '');
        $debt_date = date_timestamp_get($date);

        return $debt;
    }

    public static function getChildrenList($id_rono_group, $fio)
    {
        if (mb_strlen($fio, 'UTF-8') < 3) {
            return ['list' => []];
        }

        $fio = mb_strtolower($fio, 'UTF-8');

        $url = API_URL . self::get_API_URL('PPP_URL_CHILDREN') . $id_rono_group;
        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);

        
        if (($xml === false) || ($xml === null)) {
            return ['list' => []];
        }

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $name = $row_elem[$i]->FIO_RONO_CHILD . '';
            if (strpos(mb_strtolower($name, 'UTF-8'), $fio) !== false) {
                $list[] = [
                    'name' => $name,
                    'id'   => $row_elem[$i]->ID_RONO_CHILD . ''
                ];
            }
        }

        return ['list' => $list];
    }

    public static function getClassesList($id_sad)
    {
        $url = API_URL . self::get_API_URL('PPP_URL_CLASSES') . $id_sad;
        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);
        
        if (($xml === false) || ($xml === null)) {
            return ['list' => []];
        }

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $list[] = [
                'name' => $row_elem[$i]->NAME_RONO_GROUP . '',
                'id'   => $row_elem[$i]->ID_RONO_GROUP . ''
            ];
        }

        return ['list' => $list];
    }

    public static function getInstitutionList($id_firme)
    {
        $url = API_URL . self::get_API_URL('PPP_URL_INSTITUTION') . $id_firme;
        $xml = Http::getXmlByUrl($url);

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $list[] = [
                'NAME_SAD' => $row_elem[$i]->NAME_SAD . '',
                'R101'     => $row_elem[$i]->ID_SAD . ''
            ];
        }

        return $list;
    }

    /**
     * Функция посылает запрос на ppp с реквизитами человека. В ответ получаем данные для платежа
     *
    */
    public static function pppCreatePayment($idarea, $firme, $summ, $user_id, $r1, $r2, $r101, $r102, $r103)
    {
        $url = API_URL . self::get_API_URL('PPP_URL_CREATE');
        $summ = str_replace('.', ',', $summ);
        $timestamp = microtime(true);

        ShoppingCart::pppGetCashierByKassId(ShoppingCart::KASS_ID_TAS, $login, $password);

        $url .= $login;
        $url .= '&pwd=' . $password;
        $url .= '&idarea=' . $idarea;
        $url .= '&id_firme=' . $firme;
        $url .= '&summ=' . $summ;
        $url .= '&idsiteuser=' . $user_id;
        $url .= '&r1='   . rawurlencode(iconv('UTF-8', 'CP1251', $r1));
        $url .= '&r2='   . rawurlencode(iconv('UTF-8', 'CP1251', $r2));
        $url .= '&r101=' . $r101;
        $url .= '&r102=' . rawurlencode(iconv('UTF-8', 'CP1251', $r102));
        $url .= '&r103=' . rawurlencode(iconv('UTF-8', 'CP1251', $r103));

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

        if (($xml === null) || ($xml === false)) {
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports/kinders');
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        }
        
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;
        $err = $row_elem->ERR.'';

        if ($err != '0') {
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports/kinders');
            throw new Exception(UPC::get_error($err));
        }
        
        $insert = [
            'user_id'                  => $user_id,
            'acq'                      => $row_elem->ACQ.'',
            'timestamp'                => $timestamp,
            'type'                     => 'kinders',
            'count_services'           => 1,
            'processing'               => 'tas',
            'summ_komis'               => floatval($row_elem->SUMM_KOMIS.'') / 100,
            'summ_total'               => floatval($row_elem->SUMM_TOTAL.'') / 100,
            'reports_id_pack'          => $row_elem->ID_PACK.'',
            'reports_num_kvit'         => $row_elem->NUM_KVIT.'',
            'reports_id_plat_klient'   => $row_elem->ID_PLAT_KLIENT.'',
            'send_payment_to_reports'  => 1,
            'ip'                       => USER_REAL_IP,
            'user_agent_string'        => HTTP_USER_AGENT,
        ];
        $insert['summ_plat'] = round($insert['summ_total'] - $insert['summ_komis'], 2);

        $payment_id = PDO_DB::insert($insert, ShoppingCart::TABLE);
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        ShoppingCart::logRequestToReports($message_to_log, $payment_id, true, 'new', 'reports/kinders');

        $data = [
            'r1'     => $r1,
            'r2'     => $r2,
            'r3'     => $r3,
            'r4'     => $r4,
            'r5'     => $r5,
            'r6'     => $r6,
            'r7'     => $r7,
            'r8'     => $r8,
            'r9'     => $r9,
            'r10'    => $r10,
            'idarea' => $idarea,
        ];
        $xml_fields = ['OUTBANK', 'NAME_OWNER_BANK', 'NAME_BANK', 'NAME_PLAT', 'DST_NAME', 'DST_MFO', 'DST_OKPO', 'DST_RCOUNT', 'DST_NAME_BANK', 'DEST'];

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
            'data'       => json_encode($data),
        ];
        PDO_DB::insert($service, ShoppingCart::SERVICE_TABLE);

        return $payment;
    }
}
