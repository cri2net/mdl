<?php

use cri2net\php_pdo_db\PDO_DB;

class Budget
{
    public static function get_API_URL($key, $replace = [])
    {
        $urls = [];
        $urls['state']       = '/reports/rwservlet?report=gerc_api/spr_state.rep&cmdkey=api_test';
        $urls['area']        = '/reports/rwservlet?report=gerc_api/spr_area.rep&cmdkey=api_test&id_state={id_state}';
        $urls['plat']        = '/reports/rwservlet?report=gerc_api/spr_plat_22.rep&cmdkey=api_test&id_area={id_area}';
        $urls['firme']       = '/reports/rwservlet?report=gerc_api/spr_firme_22.rep&cmdkey=api_test&id_area={id_area}&id_plat={id_plat}';
        $urls['api_search']  = '/reports/rwservlet?report=gerc_api/api_search_abc.rep&cmdkey=api_test&abc={abc}';
        $urls['new_payment'] = '/reports/rwservlet?report=gerc_api/api_pnew_budget.rep&cmdkey=api_test';

        $url = API_URL . $urls[$key];

        foreach ($replace as $key => $value) {
            $url = str_ireplace('{' . $key . '}', $value, $url);
        }

        ShoppingCart::pppGetCashierByProcessing('tas', $login, $password);

        $url .= '&login=' . $login;
        $url .= '&pwd=' . $password;

        return $url;
    }

    /**
     * Поиск по лицевому счёту
     * @param  string $abc номер лицевого счёта
     * @return array       массив найденных услуг
     */
    public static function apiSearch($abc)
    {
        $url = self::get_API_URL('api_search', ['abc' => rawurlencode($abc)]);
        $xml_string = file_get_contents($url);
        $xml = simplexml_load_string($xml_string);

        if (($xml === false) || ($xml === null)) {
            throw new Exception('Некорректный XML');
            return false;
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        $keys = ['DATE_D', 'NAME_FIRME', 'NAME_PLAT', 'ABCOUNT', 'ADDRESS', 'FIO', 'PLAT_CODE'];
        $result = [];

        for ($i=0; $i < count($row_elem); $i++) {
            
            $arr = [];
            foreach ($keys as $key) {
                $arr[strtolower($key)] = $row_elem[$i]->$key . '';
            }

            $result[] = $arr;
        }

        return $result;
    }

    /**
     * Получает список областей от оракла
     * @return array
     */
    public static function getStates()
    {
        $url = self::get_API_URL('state');
        $xml_string = file_get_contents($url);
        $xml = simplexml_load_string($xml_string);

        if (($xml === false) || ($xml === null)) {
            throw new Exception('Некорректный XML');
            return [];
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        $result = [];
        for ($i=0; $i < count($row_elem); $i++) {
            $result[] = [
                'name' => $row_elem[$i]->NAME_STATE . '',
                'id'   => $row_elem[$i]->ID_STATE . '',
            ];
        }

        return $result;
    }

    /**
     * Получает список районов в области
     * @param  integer $id_state ID области
     * @return array
     */
    public static function getAreas($id_state)
    {
        $url = self::get_API_URL('area', ['id_state' => $id_state]);
        $xml_string = file_get_contents($url);
        $xml = simplexml_load_string($xml_string);

        if (($xml === false) || ($xml === null)) {
            throw new Exception('Некорректный XML');
            return [];
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        $result = [];
        for ($i=0; $i < count($row_elem); $i++) {
            $result[] = [
                'name' => $row_elem[$i]->NAME_AREA . '',
                'id'   => $row_elem[$i]->ID_AREA . '',
            ];
        }

        return $result;
    }

    /**
     * Получает список услуг в районе
     * @param  integer $id_area ID района
     * @return array
     */
    public static function getPlatList($id_area)
    {
        $url = self::get_API_URL('plat', ['id_area' => $id_area]);
        $xml_string = file_get_contents($url);
        $xml = simplexml_load_string($xml_string);
        
        if (($xml === false) || ($xml === null)) {
            throw new Exception('Некорректный XML');
            return false;
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        $result = [];
        for ($i=0; $i < count($row_elem); $i++) {
            $result[] = [
                'name' => $row_elem[$i]->NAME_PLAT . '',
                'id'   => $row_elem[$i]->ID_PLAT . '',
            ];
        }

        return $result;
    }

    /**
     * Получает список получателей по услуге в районе
     * @param  integer $id_area ID района
     * @param  integer $id_plat ID услуги в районе
     * @return array
     */
    public static function getFirmsList($id_area, $id_plat)
    {
        $url = self::get_API_URL('firme', ['id_area' => $id_area, 'id_plat' => $id_plat]);
        $xml_string = file_get_contents($url);
        $xml = simplexml_load_string($xml_string);
        
        if (($xml === false) || ($xml === null)) {
            throw new Exception('Некорректный XML');
            return [];
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        $result = [];
        for ($i=0; $i < count($row_elem); $i++) {
            $result[] = [
                'name' => $row_elem[$i]->NAME_FIRME . '',
                'id'   => $row_elem[$i]->ID_FIRME . '',
            ];
        }

        return $result;
    }

    /**
     * Создание платежа по реквизитам
     * @param  integer $id_area  ID района
     * @param  integer $id_firme ID получателя
     * @param  integer $id_plat  ID услуги
     * @param  integer $summ     сумма в копейках
     * @param  integer $user_id  ID пользователя на сайте
     * @param  string $fio      ФИО плательщика
     * @param  string $inn      ИНН плательщика
     * @param  string $address  адрес плательщика
     * 
     * @return assoc array of payment
     */
    public static function createPayment($id_area, $id_firme, $id_plat, $summ, $user_id, $fio, $inn, $address)
    {
        $url = self::get_API_URL('new_payment');
        $url .= '&idarea='     . rawurlencode($id_area);
        $url .= '&id_firme='   . rawurlencode($id_firme);
        $url .= '&id_plat='    . rawurlencode($id_plat);
        $url .= '&summ='       . $summ;
        $url .= '&idsiteuser=' . $user_id;
        $url .= '&r1='         . rawurlencode($fio);
        $url .= '&r2='         . rawurlencode($address);
        $url .= '&r3='         . rawurlencode($inn);

        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = simplexml_load_string($xml_string);

        $message_to_log = var_export(
            [
                'date' => date('Y-m-d H:i:s'),
                'timestamp' => microtime(true),
                'reports_url' => $url,
                'answer' => $xml_string,
            ],
            true
        );
        
        if (($xml === false) || ($xml === null)) {
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports_new/direct');
            throw new Exception('Некорректный XML');
            return [];
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;
        $err = $row_elem->ERR.'';

        if ($err != '0') {
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports_new/direct');
            throw new Exception("Ошибка создания платежа #" . $row_elem->ERR);
            return [];
        }

        ShoppingCart::logRequestToReports($message_to_log, '', true, 'new', 'reports_new/direct');


        $insert = [
            'user_id'                => $user_id,
            'timestamp'              => microtime(true),
            'summ_plat'              => $summ / 100,
            'type'                   => 'budget',
            'processing'             => 'tas',
            'ip'                     => USER_REAL_IP,
            'user_agent_string'      => HTTP_USER_AGENT,
            'reports_id_pack'        => $row_elem->ID_PACK . '',
            'reports_id_plat_klient' => $row_elem->ID_PLAT_KLIENT . '',
        ];
        
        $xml_fields = array('SUMM_KOMIS', 'SUMM_TOTAL', 'ACQ');

        for ($i=0; $i < count($xml_fields); $i++) {
            $field = $xml_fields[$i];
            $var = '_' . strtolower($field);
            $$var = $row_elem->$field;
            $insert[strtolower($field)] = $$var / 100;
        }

        $payment_id = PDO_DB::insert($insert, ShoppingCart::TABLE);

        $arr = [
            'payment_id'      => $payment_id,
            'user_id'         => $user_id,
            'timestamp'       => microtime(true),
            'data'            => json_encode([
                'id_area'         => $id_area,
                'id_firme'        => $id_firme,
                'id_plat'         => $id_plat,
                'r1'              => $fio,
                'inn'             => $inn,
                'r2'              => $address,

                'name_owner_bank' => $row_elem->NAME_OWNER_BANK.'',
                'name_bank'       => $row_elem->NAME_BANK.'',
                'dst_name'        => $row_elem->DST_NAME.'',
                'dst_mfo'         => $row_elem->DST_MFO.'',
                'dst_okpo'        => $row_elem->DST_OKPO.'',
                'dst_rcount'      => $row_elem->DST_RCOUNT.'',
                'dst_name_bank'   => $row_elem->DST_NAME_BANK.'',
                'dest'            => $row_elem->DEST.'',
            ], JSON_UNESCAPED_UNICODE),
        ];
        PDO_DB::insert($arr, ShoppingCart::SERVICE_TABLE);

        return PDO_DB::row_by_id(TABLE_PREFIX . 'payment', $payment_id);
    }
}
