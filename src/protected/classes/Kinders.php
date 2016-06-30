<?php

class Kinders
{
    const PPP_URL_INSTITUTION = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=site_api/dic_rono_sad.rep&cmdkey=api_test&id_firme=';
    const PPP_URL_CLASSES     = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=site_api/dic_rono_group&cmdkey=api_test&id_sad=';
    const PPP_URL_CHILDREN    = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=site_api/dic_rono_child&cmdkey=api_test&id_rono_group=';
    CONST PPP_URL_DEBT        = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=gerc_api/get_rono_debt&cmdkey=api_test&login=';

    const PPP_URL_CREATE      = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=gerc_api/api_pnew_rono.rep&cmdkey=api_test&login=';
    const PPP_URL_PROV        = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=gerc_api/api_prov.rep&cmdkey=api_test&login=';
    const PPP_URL_ERROR       = 'https://ppp.gerc.ua:4445/reports/rwservlet?report=gerc_api/api_pacq50.rep&cmdkey=api_test&login=';
    
    public $bank;
    
    public function __construct($bank = 'tas')
    {
        $this->bank = $bank;
    }

    private function getLogin()
    {
        switch ($this->bank) {
            case 'tas':  return 'GERCUA';
            
            default:
                throw new Exception("Unknow bank");
        }
    }

    private function getPassword()
    {
        switch ($this->bank) {
            case 'tas':  return 'B7300BFB9411B748A291A37F4E815809D81AB8FA';
            
            default:
                throw new Exception("Unknow bank");
        }
    }

    public function getDebt($child_id, &$debt_date = null)
    {
        $url = self::PPP_URL_DEBT . $this->getLogin() . '&pwd=' . $this->getPassword() . '&id_rono_child=' . $child_id;
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

    public function getChildrenList($id_rono_group, $fio)
    {
        if (mb_strlen($fio, 'UTF-8') < 3) {
            return array('list' => array());
        }

        $fio = mb_strtolower($fio, 'UTF-8');

        $url = self::PPP_URL_CHILDREN . $id_rono_group;
        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);
        
        if ($xml === false) {
            return array('list' => array());
        }

        $list = array();

        for ($i=0; $i < count($xml->ROW); $i++) {
            $name = $xml->ROW[$i]->FIO_RONO_CHILD . '';
            if (strpos(mb_strtolower($name, 'UTF-8'), $fio) !== false) {
                $list[] = array(
                    'name' => $name,
                    'id' => $xml->ROW[$i]->ID_RONO_CHILD . ''
                );
            }
        }

        return array('list' => $list);
    }

    public function getClassesList($id_sad)
    {
        $url = self::PPP_URL_CLASSES . $id_sad;
        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);
        
        if ($xml === false) {
            return array('list' => array());
        }

        $list = array();

        for ($i=0; $i < count($xml->ROW); $i++) {
            $list[] = array(
                'name' => $xml->ROW[$i]->NAME_RONO_GROUP . '',
                'id' => $xml->ROW[$i]->ID_RONO_GROUP . ''
            );
        }

        return array('list' => $list);
    }

    public function getInstitutionList($id_firme)
    {
        $url = self::PPP_URL_INSTITUTION . $id_firme;
        $xml_string = file_get_contents($url);
        $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        $xml = @simplexml_load_string($xml_string);
        
        if ($xml === false) {
            return array();
        }

        $list = array();

        for ($i=0; $i < count($xml->ROW); $i++) {
            $list[] = array(
                'NAME_SAD' => $xml->ROW[$i]->NAME_SAD . '',
                'R101' => $xml->ROW[$i]->ID_SAD . ''
            );
        }

        return $list;
    }

    /**
     * Функция посылает запрос на ppp с реквизитами человека. В ответ получаем данные для платежа
     *
    */
    public function pppCreatePayment(&$error_str, $idarea, $firme, $summ, $user_id, $r1, $r2, $r101, $r102, $r103)
    {
        // $url = self::PPP_URL_CREATE;
        // $summ .= '';
        // $summ = str_replace('.', ',', $summ);

        // $url .= $this->getLogin();
        // $url .= '&pwd=' . $this->getPassword();
        // $url .= '&idarea=' . $idarea;
        // $url .= '&id_firme=' . $firme;
        // $url .= '&summ=' . $summ;
        // $url .= '&idsiteuser=' . $user_id;
        // $url .= '&r1='   . rawurlencode(iconv('UTF-8', 'CP1251', $r1));
        // $url .= '&r2='   . rawurlencode(iconv('UTF-8', 'CP1251', $r2));
        // $url .= '&r101=' . $r101;
        // $url .= '&r102=' . rawurlencode(iconv('UTF-8', 'CP1251', $r102));
        // $url .= '&r103=' . rawurlencode(iconv('UTF-8', 'CP1251', $r103));

        // $xml_string = file_get_contents($url);
        // $xml_string = iconv('CP1251', 'UTF-8', $xml_string);
        $xml_string = str_ireplace('<?xml version="1.0" encoding="WINDOWS-1251"?>', '<?xml version="1.0" encoding="utf-8"?>', $xml_string);
        // $xml = @simplexml_load_string($xml_string);
        
        // if ($xml === false) {
        //     $error_str = 'Некорректный XML';
        //     return false;
        // }

        // $row_elem = (isset($xml->ROWSET->ROW)) ? $xml->ROWSET->ROW : $xml->ROW;
        // $err = $row_elem->ERR.'';

        // if ($err != '0') {
        //     $error_str = UPC::get_error($err);
        //     return false;
        // }




        $insert = [
            'user_id'                  => $user_id,
            'acq'                      => $row_elem->ACQ.'',
            'timestamp'                => $timestamp,
            'type'                     => 'gai',
            'count_services'           => 1,
            'processing'               => $this->bank,
            'summ_komis'               => floatval($row_elem->SUMM_KOMIS.'') / 100,
            'summ_plat'                => floatval($row_elem->SUMM_PLAT.'')  / 100,
            'summ_total'               => floatval($row_elem->SUMM_TOTAL.'') / 100,
            'reports_id_pack'          => $row_elem->ID_PACK.'',
            'reports_num_kvit'         => $row_elem->NUM_KVIT.'',
            'reports_id_plat_klient'   => $row_elem->ID_PLAT_KLIENT.'',
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
        $xml_fields = ['OUTBANK', 'NAME_OWNER_BANK', 'NAME_BANK', /*'NAME_PLAT',*/ 'DST_NAME', 'DST_MFO', 'DST_OKPO', 'DST_RCOUNT', 'DST_NAME_BANK', 'DEST'];

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
