<?php

class Gai
{
    public $bank;

    private $ppp_url           = 'https://bank.gioc.kiev.ua/reports/rwservlet?report=site_api/pnew_gai.rep&destype=Cache&Desformat=xml&cmdkey=rep';
    private $ppp_url_pdf       = 'https://bank.gioc.kiev.ua/reports/rwservlet?report=ppp/kvdbl9.rep&destype=Cache&Desformat=pdf&cmdkey=rep';
    private $ppp_history_url   = 'https://bank.gioc.kiev.ua/reports/rwservlet?report=ppp/kvdbl9hist.rep&destype=Cache&Desformat=pdf&cmdkey=rep';
    private $ppp_url_pdf_first = 'https://bank.gioc.kiev.ua/reports/rwservlet?report=ppp/kv9_pack.rep&destype=cache&Desformat=pdf&cmdkey=rep';
    
    public function __construct($bank = 'tas')
    {
        $this->bank = $bank;
    }

    public static function getRegions()
    {
        return [
            ['NAME_STATE' => 'Київська область',          'NAME_FIRM' => 'ГУК у Київ.обл./Київ.обл./',     'ID_AREA' => 233],
            ['NAME_STATE' => 'Вінницька область',         'NAME_FIRM' => 'ГУК у Вінницькій обл./Він.обл/', 'ID_AREA' => 72],
            ['NAME_STATE' => 'Волинська область',         'NAME_FIRM' => 'ГУК у Волин.обл/Волинська.обл/', 'ID_AREA' => 100],
            ['NAME_STATE' => 'Дніпропетровська область',  'NAME_FIRM' => 'ГУК у Днiпр-кiй обл/Дн-ка об/',  'ID_AREA' => 117],
            ['NAME_STATE' => 'Донецька область',          'NAME_FIRM' => 'Донецьке УК/Дон.Обл./',          'ID_AREA' => 140],
            ['NAME_STATE' => 'Житомирська область',       'NAME_FIRM' => 'ГУК у Житомир обл/Житомир обл/', 'ID_AREA' => 159],
            ['NAME_STATE' => 'Закарпатська область',      'NAME_FIRM' => 'ГУК у Закарп.обл/Закарп. обл./', 'ID_AREA' => 183],
            ['NAME_STATE' => 'Запорізька область',        'NAME_FIRM' => 'ГУК у Зап.обл./Зап.обл./',       'ID_AREA' => 197],
            ['NAME_STATE' => 'Івано-Франківська область', 'NAME_FIRM' => 'ГУК в Iв.-Франк.об/Iв.-Фран.о/', 'ID_AREA' => 218],
            ['NAME_STATE' => 'Кіровоградська область',    'NAME_FIRM' => 'ГУК у Кіров.обл./Кіров.обл./',   'ID_AREA' => 259],
            ['NAME_STATE' => 'Луганська область',         'NAME_FIRM' => 'ГУК у Луг.обл./Луганська обл./', 'ID_AREA' => 281],
            ['NAME_STATE' => 'Львівська область',         'NAME_FIRM' => 'ГУК у Львiв. обл./Львів. обл/',  'ID_AREA' => 300],
            ['NAME_STATE' => 'Миколаївська область',      'NAME_FIRM' => 'Миколаївське ГУК/Микол.обл./',   'ID_AREA' => 321],
            ['NAME_STATE' => 'Одеська область',           'NAME_FIRM' => 'ГУК в Од.обл./',                 'ID_AREA' => 1],
            ['NAME_STATE' => 'Полтавська область',        'NAME_FIRM' => 'ГУК Полтав.обл/Полтавська/',     'ID_AREA' => 341],
            ['NAME_STATE' => 'Рівненська область',        'NAME_FIRM' => 'ГУК у Рівнен.обл./Рівнен.обл./', 'ID_AREA' => 367],
            ['NAME_STATE' => 'Сумська область',           'NAME_FIRM' => 'ГУК у Сумській обл/Сумська обл/','ID_AREA' => 384],
            ['NAME_STATE' => 'Тернопільська область',     'NAME_FIRM' => 'ГУК у Терноп.обл./Терноп.обл./', 'ID_AREA' => 403],
            ['NAME_STATE' => 'Харківська область',        'NAME_FIRM' => 'ГУК Харківськ обл/Харкiвобл/',   'ID_AREA' => 421],
            ['NAME_STATE' => 'Херсонська область',        'NAME_FIRM' => 'ГУК у Херсон обл/Херсон обл/',   'ID_AREA' => 449],
            ['NAME_STATE' => 'Хмельницька область',       'NAME_FIRM' => 'ГУК у Хмельниц.обл/Хмел.обл/',   'ID_AREA' => 468],
            ['NAME_STATE' => 'Черкаська область',         'NAME_FIRM' => 'ГУК у Черк.обл./Черкаська обл/', 'ID_AREA' => 489],
            ['NAME_STATE' => 'Чернівецька область',       'NAME_FIRM' => 'Чернівецьке ГУК/Чернiв.обл/',    'ID_AREA' => 510],
            ['NAME_STATE' => 'Чернігівська область',      'NAME_FIRM' => 'ГУК у Чернігів.обл/Черніг.обл/', 'ID_AREA' => 522],
        ];
    }

    /**
     * По ID кассы получаем логин и хеш пароля кассира
     * @param  string  $login    Логин кассира (SHA1, кажется)
     * @param  string  $password Хеш пароля кассира
     * 
     * @return void
     */
    public static function pppGetCashierByKassId(&$login, &$password)
    {
        // switch ($this->bank) {
        //     case 'tas':
                $login    = 'GIOCKIEVUA';
                $password = '7D107006F752860E6FAEBD84156A676B8852C439';
                // break;
        // }
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
    public function set_request_to_ppp(&$error_str, $idarea, $summ, $user_id, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $r8, $r9, $r10)
    {
        $url = $this->ppp_url;
        $timestamp = microtime(true);

        $summ .= '';
        $summ = str_replace('.', ',', $summ);

        self::pppGetCashierByKassId($login, $password);

        $url .= '&login=' . $login;
        $url .= '&idarea=' . $idarea;
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
        
        if ($xml === false) {
            $error_str = 'Некорректный XML';
            return false;
        }

        $err = $xml->ROW->ERR.'';

        if ($err != '0') {
            $error_str = UPC::get_error($err);
            return false;
        }

        $insert = [
            'user_id'                  => $user_id,
            'acq'                      => $xml->ROW->ACQ.'',
            'timestamp'                => $timestamp,
            'type'                     => 'gai',
            'count_services'           => 1,
            'processing'               => $this->bank,
            'summ_komis'               => floatval($xml->ROW->SUMM_KOMIS.'') / 100,
            'summ_plat'                => floatval($xml->ROW->SUMM_PLAT.'')  / 100,
            'summ_total'               => floatval($xml->ROW->SUMM_TOTAL.'') / 100,
            'reports_id_pack'          => $xml->ROW->ID_PACK.'',
            'reports_num_kvit'         => $xml->ROW->NUM_KVIT.'',
            'reports_id_plat_klient'   => $xml->ROW->ID_PLAT_KLIENT.'',
            'send_payment_to_reports'  => 1,
            'ip'                       => USER_REAL_IP,
            'user_agent_string'        => HTTP_USER_AGENT,
        ];

        $payment_id = PDO_DB::insert($insert, ShoppingCart::TABLE);
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);

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

    public function check_order($id)
    {
        $transaction = self::get_transaction($id);

        if (!$transaction || ($transaction['status'] != 0)) {
            return;
        }

        if ($transaction['processing'] == 'tas') {
            $TasLink = new TasLink('budget');
            $TasLink->checkStatus('g' . $id);
            return;
        }
    }

    public static function get_transaction($id)
    {
        return PDO_DB::row_by_id(ShoppingCart::TABLE, str_replace('gioc-', '', $id));
    }
    
    public function getPDF($id, $is_first = false)
    {
        $record = self::get_transaction($id);

        if (!$record) {
            return;
        }

        if ($is_first) {
            $url = $this->ppp_url_pdf_first . '&id_p=' . $record['id_pack'];
            return Http::httpGet($url);
        }

        $first_pdf  = Http::httpGet($this->ppp_url_pdf     . '&id_k=' . $record['id_plat_klient'] . '&num_group=' . $record['num_kvit']);
        $second_pdf = Http::httpGet($this->ppp_history_url . '&id_k=' . $record['id_plat_klient'] . '&num_group=' . $record['num_kvit']);

        return (strlen($first_pdf) > strlen($second_pdf)) ? $first_pdf : $second_pdf;
    }
}
