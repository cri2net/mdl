<?php

use cri2net\php_pdo_db\PDO_DB;

class Gai
{
    const PPP_URL        = '/reports/rwservlet?report=gerc_api/api_pnew_gai_fine.rep&destype=Cache&Desformat=xml&cmdkey=rep';
    const PPP_URL_REGION = '/reports/rwservlet?report=gerc_api/spr_firme_661.rep&destype=Cache&Desformat=xml&cmdkey=rep';
    
    public static function getRegions()
    {
        $url = API_URL . self::PPP_URL_REGION;
        try {
            $xml = Http::getXmlByUrl($url);
        } catch (Exception $e) {
            return ['list' => []];
        }

        $list = [];
        $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;

        for ($i=0; $i < count($row_elem); $i++) {
            $list[] = [
                'NAME_STATE' => $row_elem[$i]->NAME_STATE . '',
                'NAME_FIRME' => $row_elem[$i]->NAME_FIRME . '',
                'ID_AREA'    => $row_elem[$i]->ID_FIRME . '',
            ];

            // ГУК для киевской области и г. Киев имеет одинаковое поле NAME_STATE
            // хардкодно меняю его, чтоб люди не путались
            if ($row_elem[$i]->NAME_FIRME == 'ГУК у м.Києві/м.Київ/') {
                $list[count($list) - 1]['NAME_STATE'] .= ' (м.Київ)';
            }
        }

        return $list;
    }

    /**
     * Функция посылает запрос на ppp с реквизитами человека. В ответ получаем данные для платежа
     *
     * @param integer $idarea — номер региона (области)
     * @param double $summ — Сумма штрафа
     * @param integer $user_id — id пользователя в системе ГЕРЦ
     * @param string $r1   — ПІБ
     * @param string $r2   — АДРЕСА
     * @param string $r3   — ІНН
     * @param string $r4   — ДЕРЖ.НОМЕР
     * @param string $r5   — СЕРІЯ ПРОТОК.
     * @param string $r6   — НОМЕР ПРОТОК.
     * @param string $r7   — СЕРІЯ ПОСТАНОВ.
     * @param string $r8   — НОМЕР ПОСТАНОВ.
     * @param string $r9   — ДАТА ПРОТОК.
     * @param string $r10  — ДАТА ПОСТАНОВ.
    */
    public static function set_request_to_ppp($id_firme, $summ, $user_id, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $r8, $r9, $r10)
    {
        $url = API_URL . self::PPP_URL;
        $timestamp = microtime(true);

        $summ .= '';
        $summ = str_replace('.', ',', $summ);

        ShoppingCart::pppGetCashierByProcessing('tas', $login, $password);

        $url .= '&login=' . $login;
        $url .= '&pwd=' . $password;
        $url .= '&id_firme=' . $id_firme;
        $url .= '&summ=' . $summ;
        $url .= '&idsiteuser=' . $user_id;
        $url .= '&r1=' . rawurlencode(iconv('UTF-8', 'CP1251', $r1));
        $url .= '&r2=' . rawurlencode(iconv('UTF-8', 'CP1251', $r2));
        $url .= '&r3=' . rawurlencode(iconv('UTF-8', 'CP1251', $r3));
        $url .= '&r4=' . rawurlencode(iconv('UTF-8', 'CP1251', $r4));
        $url .= '&r5=' . rawurlencode(iconv('UTF-8', 'CP1251', $r5));
        $url .= '&r6=' . rawurlencode(iconv('UTF-8', 'CP1251', $r6));
        $url .= '&r7=' . rawurlencode(iconv('UTF-8', 'CP1251', $r7));
        $url .= '&r8=' . rawurlencode(iconv('UTF-8', 'CP1251', $r8));
        $url .= '&r9=' . rawurlencode(iconv('UTF-8', 'CP1251', $r9));
        $url .= '&r10=' . rawurlencode(iconv('UTF-8', 'CP1251', $r10));

        $xml_string = Http::fgets($url);
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
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports/gai');
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        }

        $err = $xml->ROW->ERR.'';

        if ($err != '0') {
            ShoppingCart::logRequestToReports($message_to_log, '', false, 'new', 'reports/gai');
            throw new Exception(UPC::get_error($err));
        }

        $insert = [
            'user_id'                 => $user_id,
            'acq'                     => $xml->ROW->ACQ.'',
            'timestamp'               => $timestamp,
            'type'                    => 'gai',
            'count_services'          => 1,
            'processing'              => 'tas',
            'summ_komis'              => floatval($xml->ROW->SUMM_KOMIS.'') / 100,
            'summ_total'              => floatval($xml->ROW->SUMM_TOTAL.'') / 100,
            'reports_id_pack'         => $xml->ROW->ID_PACK.'',
            'reports_id_plat_klient'  => $xml->ROW->ID_PLAT_KLIENT.'',
            'send_payment_to_reports' => 1,
            'ip'                      => USER_REAL_IP,
            'user_agent_string'       => HTTP_USER_AGENT,
        ];
        $insert['summ_plat'] = $insert['summ_total'] - $insert['summ_komis'];

        $payment_id = PDO_DB::insert($insert, ShoppingCart::TABLE);
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        ShoppingCart::logRequestToReports($message_to_log, $payment_id, true, 'new', 'reports/gai');

        $data = [
            'r1'       => $r1,
            'r2'       => $r2,
            'r3'       => $r3,
            'r4'       => $r4,
            'r5'       => $r5,
            'r6'       => $r6,
            'r7'       => $r7,
            'r8'       => $r8,
            'r9'       => $r9,
            'r10'      => $r10,
            'id_firme' => $id_firme,
        ];
        $xml_fields = ['OUTBANK', 'NAME_OWNER_BANK', 'NAME_BANK', 'NAME_PLAT', 'DST_NAME', 'DST_MFO', 'DST_OKPO', 'DST_RCOUNT', 'DST_NAME_BANK', 'DEST'];

        for ($i=0; $i < count($xml_fields); $i++) {
            $field = $xml_fields[$i];
            $var = '_' . strtolower($field);
            $$var = $xml->ROW->$field . '';
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
