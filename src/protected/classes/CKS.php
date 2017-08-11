<?php

use cri2net\php_pdo_db\PDO_DB;

class CKS
{
    const PLAT_LIST        = '/reports/rwservlet?report=gerc_api/spr_plat_49.rep&cmdkey=rep&destype=Cache&Desformat=xml';
    const FIRME_LIST       = '/reports/rwservlet?report=gerc_api/spr_firme_49.rep&cmdkey=rep&destype=Cache&Desformat=xml&id_plat=';
    const CREATE_PAYMENT   = '/reports/rwservlet?report=gerc_api/api_pnew_cks.rep&cmdkey=rep&destype=Cache&Desformat=xml&login=';
    const CASHIER_LOGIN    = 'cks_site_com';
    const CASHIER_PASSWORD = '0168F38DC7806216FB81C0CEAEF044713A1397BE';

    /**
     * Отдаёт список районов, в которых есть отделения ЦКС
     * 
     * По идее, в любом отделении можно принимать все услуги, оракл нам отдаёт в другом порядке:
     * список услуг, а по каждой уже где её можна принимать
     * 
     * @return array
     */
    public static function getDistricts()
    {
        $plat_list = self::getPlatList();
        $firme_list = self::getFirmeList($plat_list[0]['id']);
        $districts = [];

        // теперь парсим почти сырой адрес отделений
        foreach ($firme_list as $item) {

            $district_name = mb_substr($item['name'], 0, mb_strpos($item['name'], ',', 0, 'UTF-8'), 'UTF-8');
            $key = mb_strtolower(trim($district_name), 'UTF-8');

            if (!isset($districts[$key])) {
                $districts[$key] = [
                    'name'       => trim($district_name),
                    'firme_list' => [],
                ];
            }

            $districts[$key]['firme_list'][] = [
                'name' => trim(substr($item['name'], strlen($district_name) + 1)),
                'id'   => $item['id'],
            ];
        }

        return $districts;
    }

    public static function getPlatList()
    {
        $xml = Http::getXmlByUrl(API_URL . self::PLAT_LIST);

        $result = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $result[] = [
                'name' => $row_elem[$i]->NAME_PLAT . '',
                'sum'  => intval($row_elem[$i]->SUMM_PLAT . '') / 100,
                'id'   => $row_elem[$i]->ID_PLAT . '',
            ];
        }

        return $result;
    }

    public static function getFirmeList($plat_id)
    {
        $xml = Http::getXmlByUrl(API_URL . self::FIRME_LIST . $plat_id);

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

    /**
     * Создание платежа в оракле
     * 
     * @param  array   $plat_list Массив с услугами
     * @param  integer $firme_id  ID получателя
     * @param  string  $sum_list  Список сум за каждую услугу через запятую
     * @param  string  $username  ФИО плательщика
     * @param  string  $address   Адрес плательщика
     * @param  integer $user_id   ID пользователя
     * 
     * @return array new payment
     */
    public static function createPayment(array $plat_list, $firme_id, $sum_list, $username, $address, $user_id)
    {
        $all_plat_list  = self::getPlatList();
        $all_firme_list = self::getFirmeList($all_plat_list[0]['id']);

        $plat_id = '';
        foreach ($plat_list as $plat_item) {
            $plat_id .= $plat_item['id'] . ',';
        }

        $time = microtime(true);

        $plat_id = trim($plat_id, ',');

        $url = API_URL . self::CREATE_PAYMENT . self::CASHIER_LOGIN;
        $url .= '&pwd=' . self::CASHIER_PASSWORD;
        $url .= '&id_firme=' . $firme_id;
        $url .= '&id_plat=' . $plat_id;
        $url .= '&summ=' . $sum_list;
        $url .= '&idsiteuser=' . rawurlencode($user_id);
        $url .= '&r1=' . rawurlencode(iconv('UTF-8', 'CP1251', $username));
        $url .= '&r2=' . rawurlencode(iconv('UTF-8', 'CP1251', $address));

        $time = microtime(true);

        $xml_string = Http::fgets($url);
        $xml = @simplexml_load_string($xml_string);

        $message_to_log = var_export(
            [
                'date'        => date('Y-m-d H:i:s'),
                'timestamp'   => $time,
                'reports_url' => $url,
                'answer'      => $xml_string,
            ],
            true
        );
        
        if (($xml === false) || ($xml === null)) {
            $error_str = 'Некорректный XML';
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports/cks');
            throw new Exception($error_str);
            return [];
        }

        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;
        $err = $row_elem->ERR.'';

        if ($err != '0') {
            $error_str = "Ошибка создания платежа #" . $row_elem->ERR;
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports_new/cks');
            throw new Exception($error_str);
            return [];
        }

        ShoppingCart::logRequestToReports($message_to_log, '', true, 'new', 'reports_new/cks');

        $insert = [
            'user_id'                 => $user_id,
            'acq'                     => $row_elem->ACQ.'',
            'timestamp'               => $time,
            'type'                    => 'cks',
            'count_services'          => count($plat_list),
            'processing'              => 'tas',
            'summ_komis'              => floatval($row_elem->SUMM_KOMIS.'') / 100,
            'summ_total'              => floatval($row_elem->SUMM_TOTAL.'') / 100,
            'reports_id_pack'         => $row_elem->ID_PACK.'',
            'reports_id_plat_klient'  => $row_elem->ID_PLAT_KLIENT.'',
            'send_payment_to_reports' => 1,
            'ip'                      => USER_REAL_IP,
            'user_agent_string'       => HTTP_USER_AGENT,
        ];
        $insert['summ_plat'] = round($insert['summ_total'] - $insert['summ_komis'], 2);

        $payment_id = PDO_DB::insert($insert, ShoppingCart::TABLE);
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);

        $data = [
            'firme_id'   => $firme_id,
            'firme_name' => $firme_id,
            'r1'         => $username,
            'r2'         => $address,
        ];

        foreach ($all_firme_list as $item) {
            if ($item['id'] == $firme_id) {
                $data['firme_item'] = $item;
                break;
            }
        }

        $xml_fields = ['OUTBANK', 'NAME_OWNER_BANK', 'NAME_BANK', 'NAME_PLAT', 'DST_NAME', 'DST_MFO', 'DST_OKPO', 'DST_RCOUNT', 'DST_NAME_BANK', 'DEST'];

        for ($i=0; $i < count($xml_fields); $i++) {
            $field = $xml_fields[$i];
            $var = '_' . strtolower($field);
            $$var = $row_elem->$field . '';
            $data[strtolower($field)] = $$var;
        }

        foreach ($plat_list as $plat_item) {

            $data['plat_id'] = $plat_item['id'];

            foreach ($all_plat_list as $item) {
                if ($item['id'] == $plat_item['id']) {
                    $data['plat_item'] = $item;
                    break;
                }
            }

            $service = [
                'payment_id' => $payment['id'],
                'user_id'    => $payment['user_id'],
                'sum'        => $plat_item['sum'],
                'timestamp'  => $time,
                'data'       => json_encode($data, JSON_UNESCAPED_UNICODE),
            ];
            PDO_DB::insert($service, ShoppingCart::SERVICE_TABLE);
        }

        return $payment;
    }
}
