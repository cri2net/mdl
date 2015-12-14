<?php

class KomDebt
{
    const ANSWERS_PATH = '/protected/conf/testing/';
    
    public $testing = false;
    protected $cache = [];
    protected $komplat_URL;
    protected $debt_URL;

    private $months;
    private $monthsFullName;
    private $beginDate;
    private $endDate;

    public function __construct()
    {
        global $MONTHS, $MONTHS_NAME;
        $this->testing = !HAVE_ACCESS_TO_API;
        $this->months = $MONTHS;
        $this->monthsFullName = $MONTHS_NAME;

        if (stristr(API_URL, 'bank.gioc')) {
            $this->komplat_URL = '/reports/rwservlet?report=/site/g_komoplat.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
            $this->debt_URL = '/reports/rwservlet?report=/site/g_komdebt.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
        } else {
            $this->komplat_URL = '/reports/rwservlet?report=g_komoplat.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
            $this->debt_URL = '/reports/rwservlet?report=g_komdebt.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
        }
    }
    
    private function getXML($url, $obj_id, $dateBegin = null)
    {
        try {
            $dateData = $this->getDatePeriod($dateBegin);
            
            if ($this->testing) {
                // в режиме тестирования мы не можем обращаться к API (скорее всего), так что берём шаблоны ответов
                if ($url == $this->debt_URL) {
                    return file_get_contents(ROOT . self::ANSWERS_PATH . 'DEBTURL.xml');
                } else {
                    return file_get_contents(ROOT . self::ANSWERS_PATH . 'KOMPLATURL.xml');
                }
            }

            $url = API_URL . $url . $obj_id . "&dbegin=" . $dateData['begin'] . "&dend=" . $dateData['end'];

            if (!isset($this->cache[md5($url)])) {
                $this->cache[md5($url)] = Http::fgets($url);
            }
            return $this->cache[md5($url)];
        } catch (Exception $e) {
            throw new Exception(ERROR_SERVICE_TEMPORARY_ERROR);
        }
    }
    
    public function clearCache()
    {
        $this->cache = [];
    }

    public function getDebtSum($obj_id, $dateBegin = null, $depth = 0, &$real_timestamp = null)
    {
        $xmlString = $this->getXML($this->debt_URL, $obj_id, $dateBegin);
        $xml = @new SimpleXMLElement($xmlString);
        
        if (isset($xml->ROW[0]->KOM_ERROR)) {
            $error = (string)$xml->ROW[0]->KOM_ERROR;
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $debt = 0;
        $have_data = false;

        foreach ($xml->xpath("//ROW") as $row) {
            if (floatval($row->SUMM_DOLG) > 0 && !$row->COUNTERS->COUNTERS_ITEM) {
                $debt += (float)$row->SUMM_DOLG;
            }
            $have_data = true;
        }
        
        if ($dateBegin == null) {
            $real_timestamp = time();
        } else {
            $real_timestamp = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
        }
        
        // maybe no data for this month
        if (($debt == 0) && !$have_data && ($depth < 4)) {
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $real_timestamp));
            return $this->getDebtSum($obj_id, $dateBegin, $depth + 1);
        }
        
        $debtStr = str_replace('.', ',', sprintf('%.2f',((float)$debt)/100));
        return $debtStr;
    }
    
    private function getDatePeriod($dateBegin = null)
    {
        if (empty($dateBegin)) {
            $this->beginDate = $beginDate = "1.".date("m.Y");
        
            if (date("n") == 12) {
                $nextMonth = 1;
                $year = date("Y") + 1;
            } else {
                $nextMonth = date("n") + 1;
                $year = date("Y");
            }
        
            $this->endDate = $endDate = "1.".$nextMonth.".".$year;
        } else {
            $this->beginDate = $beginDate = $dateBegin;
            $month = date("n", strtotime($dateBegin));
            $year = date("Y", strtotime($dateBegin));
            $month2 = date("m", strtotime($dateBegin));
           
            if (function_exists('cal_days_in_month')) {
                $this->endDate = $endDate = cal_days_in_month(CAL_GREGORIAN, $month, $year) . "." . $month2 . "." . $year;
            } else {
                switch ((int)$month2) {
                    case 4:
                    case 6:
                    case 9:
                    case 11:
                        $days_count = "30";
                        break;

                    case 2:
                        $days_count = ($year % 4 == 0) ? '29' : '28';
                        break;
                    
                    default:
                        $days_count = "31";
                }

                $this->endDate = $endDate = $days_count . "." . $month2 . "." . $year;
            }
        }
        
        return array('begin'=>$beginDate, 'end'=>$endDate);
    }
    
    private static function msort($array, $id = "counter", $sort_ascending = true)
    {
        $temp_array = [];
      
        while (count($array) > 0) {
            $lowest_id = 0;
            $index=0;
            
            foreach ($array as $item) {
                if (isset($item[$id]) && $array[$lowest_id][$id] && ($item[$id] < $array[$lowest_id][$id])) {
                    $lowest_id = $index;
                }
                $index++;
            }
            
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
        
        if ($sort_ascending) {
            return $temp_array;
        }
        return array_reverse($temp_array);
    }
    
    public function getData($obj_id, $dateBegin = null, $depth = 0)
    {
        $data = [];
        $data['date'] = '1 ' . $this->months[date("n")]['ua'] . " " . date("Y");
        $xmlString = $this->getXML($this->debt_URL, $obj_id, $dateBegin);
        
        if ($xmlString !== false) {
            $xml = @new SimpleXMLElement($xmlString);
            if (isset($xml->ROW[0]->KOM_ERROR)) {
                $error = (string)$xml->ROW[0]->KOM_ERROR;
            }
            
            if (!empty($error)) {
                throw new Exception(ERROR_GETTING_DEBT);
            }
            
            $data['dbegin']    = $this->beginDate;
            $data['dend']      = $this->endDate;
            $data['PEOPLE']    = (isset($xml->ROW[0]->PEOPLE))    ? $xml->ROW[0]->PEOPLE    : null;
            $data['PL_OB']     = (isset($xml->ROW[0]->PL_OB))     ? $xml->ROW[0]->PL_OB     : null;
            $data['PL_POL']    = (isset($xml->ROW[0]->PL_POL))    ? $xml->ROW[0]->PL_POL    : null;
            $data['PLAT_CODE'] = (isset($xml->ROW[0]->PLAT_CODE)) ? $xml->ROW[0]->PLAT_CODE : null;
            // Такие вещи, как PLAT_CODE и OUT_KEY доступны только в истории начислений
            
            $fullDept = 0;
            $data['list'] = [];

            foreach ($xml->xpath("//ROW") as $row) {
                $list = [];

                $auth_key = (isset($row->OUT_KEY)) ? ($row->OUT_KEY . '') : null;
                if ($auth_key) {
                    Flat::addAuthKey($auth_key, $obj_id);
                }
               
                $list['firm_name'] = str_replace('"', '&quot;', (string)$row->NAME_FIRME);
                $list['name_plat'] = $this->getNamePlat($row->NAME_PLAT);
                $list['CODE_FIRME'] = (string)$row->CODE_FIRME;
                $list['CODE_PLAT'] = (string)$row->CODE_PLAT;
                $list['ID_PLAT'] = (string)$row->ID_PLAT;
                $list['ABCOUNT'] = (string)$row->ABCOUNT;
                $list['PLAT_CODE'] = (string)$row->PLAT_CODE;
                $list['NAME_BANKS'] = htmlspecialchars(trim((string)$row->NAME_BANKS), ENT_QUOTES);
                $list['BANK_CODE'] = (string)$row->MFO;
                $list['DBEGIN_XML'] = date("Y-m-d", strtotime((string)$row->DBEGIN));
                $list['DEND_XML'] = date("Y-m-d", strtotime((string)$row->DEND));
                $list['DATE_D'] = (string)$row->DATE_D;
                $list['FIO'] = (string)$row->FIO;
                $list['TLF'] = (string)$row->TLF;
                $list['R_COUNT'] = (string)$row->R_COUNT;

                $SUMM_MONTH = ((float)$row->SUMM_MONTH)/100;
                if ($SUMM_MONTH <= 0) {
                    $list['SUMM_MONTH'] = '-';
                } else {
                    $list['SUMM_MONTH'] = sprintf('%.2f', $SUMM_MONTH);
                    $list['SUMM_MONTH'] = str_replace(".", ",", $list['SUMM_MONTH']);
                }

                if ($row->COUNTERS->COUNTERS_ITEM) {
                    $list['counterData']['FIRM_NAME'] = str_replace('"', '&quot;', (string)$row->NAME_FIRME);
                    $list['counterData']['CODE_FIRME'] = (int)$row->CODE_FIRME;
                    $list['counterData']['date'] = " 01.".date("n").".".date("y");
                    $list['counterData']['tarif'] = str_replace('.', ',', sprintf('%.2f',((float)$row->TARIF)/100));
                    $list['counterData']['real_tarif'] = (float)($row->TARIF/100);
                    $list['counterData']['NAME_PLAT'] = $this->getNamePlat($row->NAME_PLAT);
                    $list['counterData']['NAIM_LG'] = (string)$row->NAIM_LG;
                    $list['counterData']['PROC_LG'] = (string)$row->PROC_LG;
                    $list['counterData']['KOL_LGOT'] = (string)$row->KOL_LGOT;
                    $list['counterData']['PEOPLE'] = (int)$row->PEOPLE;

                    foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                        $list['counterData']['counters'][] = [
                            'COUNTER_NO' => (int)$counter->COUNTER_NO,
                            'OLD_VALUE' => (int)$counter->OLD_VALUE,
                            'ABCOUNTER' => (string)$counter->ABCOUNTER,
                        ];
                    }
                }
                
                $debt = ((float)$row->SUMM_DOLG)/100;
                
                if ((int)$row->SUMM_DOLG < 0) {
                    $list['over_pay'] = substr($debt, 1);
                    $list['over_pay'] = str_replace(".", ",", $list['over_pay']);
                    $list['to_pay'] = "0,00";
                    $list['debt'] = "-";
                } elseif ((int)$row->SUMM_DOLG == 0) {
                    $list['over_pay'] = "-";
                    $list['to_pay'] = "0,00";
                    $list['debt'] = "-";
                } else {
                    if (!$row->COUNTERS->COUNTERS_ITEM) {
                        $fullDept += (int)$row->SUMM_DOLG;
                    }
                    $list['over_pay'] = "-";
                    $list['to_pay'] = sprintf('%.2f', $debt);
                    $list['debt'] = sprintf('%.2f', $debt);
                    
                    $list['to_pay'] = str_replace(".", ",", $list['to_pay']);
                    $list['debt'] = str_replace(".", ",", $list['debt']);
                }
                
                if ($row->COUNTERS->COUNTERS_ITEM) {
                    $list['counter'] = 1;
                    $list['to_pay'] = "-";
                    $list['debt'] = "-";
                } else {
                    $list['counter'] = 0;
                }
                $data['list'][] = $list;
            }

            $data['list'] = $this->msort($data['list'], 'counter', true);
            $data['full_dept'] = sprintf('%.2f',((float)$fullDept)/100);
        } else {
            $data = [];
        }
        
        if ((count($data['list']) == 0) && ($depth < 1)) {
            // maybe no data for this month
            
            if ($dateBegin == null) {
                $now = time();
            } else {
                $now = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
            }
            
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $now));
            return $this->getData($obj_id, $dateBegin, $depth + 1);
        }

        // не получилось вытащить данные. Генерируем пустышку
        if (empty($data)) {
            $data['timestamp'] = time();
            $data['list'] = [];
            return $data;
        }
        
        $data['full_dept'] = str_replace(".", ",", $data['full_dept']);

        if ($data['list'][0]['DBEGIN_XML']) {
            $date = @DateTime::createFromFormat('Y-m-d', $data['list'][0]['DBEGIN_XML']);
            $data['date'] = $date->format('j ') . $this->months[$date->format('n')]['ua'] . $date->format(' Y');
        } else {
            $date = @DateTime::createFromFormat('j.m.Y', $dateBegin);
        }

        $data['timestamp'] = @date_timestamp_get($date);
        return $data;
    }

    public function getHistoryBillData($obj_id, $dateBegin = null, $depth = 0, &$real_timestamp = null, $first_real_timestamp = null)
    {
        $xmlString = $this->getXML($this->komplat_URL, $obj_id, $dateBegin);
        $xmlString = str_replace("&nbsp;", "", $xmlString);
        $xml = @new SimpleXMLElement($xmlString);
        if (isset($xml->ROW[0]->KOM_ERROR)) {
            $error = (string)$xml->ROW[0]->KOM_ERROR;
        }
    
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data = [];

        foreach ($xml->xpath("//ROW") as $row) {
            $data['bank'][(string)$row->PUNKT_ID]['NAMEOKP'] = (string)$row->NAMEOKP;
            $data['bank'][(string)$row->PUNKT_ID]['KASSA'] = (string)$row->KASSA;
            
            $dataArray = [
                'NAME_FIRME' => (string)$row->NAME_FIRME,
                'NAME_PLAT'  => $this->getNamePlat($row->NAME_PLAT),
                'SUMM'       => str_replace('.', ',', sprintf('%.2f',((float)$row->SUMM)/100)),
                'PDATE'      => date("d.m.y H:i:s", strtotime((string)$row->PDATE)),
                'ABCOUNT'    => (string)$row->ABCOUNT,
                'DBEGIN'     => (string)$row->DBEGIN1,
                'DEND'       => (string)$row->DEND1
            ];

            if ($row->CNTR->CNTR_ITEM) {
                $dataArray['counter'] = 1;
                $dataArray['OLD_VALUE'] = (string)$row->CNTR->CNTR_ITEM->OLD_VALUE;
                $dataArray['NEW_VALUE'] = (string)$row->CNTR->CNTR_ITEM->NEW_VALUE;
            }
        
            $data['bank'][(string)$row->PUNKT_ID]['data'][] = $dataArray;
        }

        if ($dateBegin == null) {
            $real_timestamp = time();
        } else {
            $real_timestamp = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
        }

        if ($first_real_timestamp == null) {
            $first_real_timestamp = $real_timestamp;
        }

        // maybe no data for this month
        if (empty($data) && ($depth < 1)) {
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $real_timestamp));
            return $this->getHistoryBillData($obj_id, $dateBegin, $depth + 1, $real_timestamp, $first_real_timestamp);
        }

        if (empty($data)) {
            $real_timestamp = $first_real_timestamp;
        }

        return $data;
    }
    
    public function getPayOnThisMonth($obj_id, $dateBegin = null, $depth = 0, &$real_timestamp = null, $first_real_timestamp = null)
    {
        $xmlString = $this->getXML($this->komplat_URL, $obj_id, $dateBegin);

        $xmlString = str_replace("&nbsp;", "", $xmlString);
        $xml = @new SimpleXMLElement($xmlString);

        if (!empty($xml->ROW[0]->KOM_ERROR)) {
            throw new Exception(ERROR_GETTING_DEBT);
            return false;
        }
        
        $summ = .0;
        foreach ($xml->xpath("//ROW") as $row) {
            $have_data = true;
            $summ += (float)($row->SUMM)/100;
        }

        if ($dateBegin == null) {
            $real_timestamp = time();
        } else {
            $real_timestamp = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
        }

        if ($first_real_timestamp == null) {
            $first_real_timestamp = $real_timestamp;
        }

        // maybe no data for this month
        if (!$have_data && ($depth < 1)) {
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $real_timestamp));
            return $this->getPayOnThisMonth($obj_id, $dateBegin, $depth + 1, $real_timestamp, $first_real_timestamp);
        }

        if (!$have_data) {
            $real_timestamp = $first_real_timestamp;
        }
        
        return str_replace(".", ",", sprintf('%.2f', $summ));
    }
    
    public function getUniqueFirmName($obj_id, $dateBegin = null, $depth = 0, &$real_timestamp = null, $first_real_timestamp = null)
    {
        $data = [];
        $xmlString = $this->getXML($this->debt_URL, $obj_id, $dateBegin);
        $xml = @new SimpleXMLElement($xmlString);
        if (isset($xml->ROW[0]->KOM_ERROR)) {
            $error = (string)$xml->ROW[0]->KOM_ERROR;
        }
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data['firm'] = [];
        $have_data = false;
        
        foreach ($xml->xpath("//ROW") as $row) {
            if (!array_key_exists((string)$row->CODE_FIRME, $data['firm'])) {
                $data['firm'][(string)$row->CODE_FIRME]['name'] = (string)$row->NAME_FIRME;
                $have_data = true;
            }
        }

        if ($dateBegin == null) {
            $real_timestamp = time();
        } else {
            $real_timestamp = date_timestamp_get(DateTime::createFromFormat('j.m.Y', $dateBegin));
        }

        if ($first_real_timestamp == null) {
            $first_real_timestamp = $real_timestamp;
        }

        // maybe no data for this month
        if (!$have_data && ($depth < 1)) {
            $dateBegin = date('1.m.Y', strtotime('first day of previous month', $real_timestamp));
            return $this->getUniqueFirmName($obj_id, $dateBegin, $depth + 1, $real_timestamp, $first_real_timestamp);
        }

        if (!$have_data) {
            $real_timestamp = $first_real_timestamp;
        }

        return  $data['firm'];
    }
    

    public function getUniqueFirmXIface($obj_id, $firmName = null, $dateBegin = null, $is_filter = false)
    {
        
        //return;
        $this->beginDate = $dateBegin;
        $pin = Flat::getFlatPINByID($obj_id);
        $pacc = (integer)substr($pin,-6);
        $jek = ($pin-$pacc)/1000000;
        
        
        $xIfaceClass = new xIfaceClass();
        $xIfaceClass->Query('Municipal-2.0', 'GetBills',
                array('QueryParams'=>array('@JEK'=>$jek, '@PACC'=>$pacc,'@IncludePayments'=> 'TRUE', '@Period' => date("Ym",strtotime($dateBegin)))),
                strtotime(date('Y-m-d 06:00:00',strtotime('+1 day')))-time()/*timeout tomorrow morning*/ );
        $resXMLObj = new SimpleXMLElement($xIfaceClass->replyXML->saveXML());
        





        //echo date("Ym",strtotime($dateBegin)) ;
        //var_dump($resXMLObj);
        $data['date'] = '01 ' . $this->months[date("n")]['ua'] . " " . date("Y");
        $data['curr_month'] = date("m");
        $data['curr_year'] = date("Y");
        $data['dateBegin'] = $dateBegin;
        
        if ((!$is_filter) && (!empty($error))) {
            //Реализовать вывод только по одному билеру
        }
        
        
        
        $data['firm'] = [];
        $data['dbegin'] = date("d.m.y", strtotime($this->beginDate));
        $data['dend'] = date("d.m.y", strtotime($this->endDate));
        
        //echo "!";
        $debtBeginMonth = DateTime::createFromFormat('j.m.Y', $this->beginDate);
        $debtBeginMonth = strtotime('first day of previous month', date_timestamp_get($debtBeginMonth));
        $data['previous_date'] = date('d.m.Y', $debtBeginMonth);

        
        $data['begin_month'] = $this->monthsFullName[date('n', strtotime($this->beginDate))]['ua']['big'];
        $data['previous_month'] = $this->monthsFullName[date('n', $debtBeginMonth)]['ua']['big'];
        $data['counter'] = 0;
        $data['PEOPLE'] = '';//$xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = '';//$xml->ROW[0]->PL_OB;
        $data['PL_POL'] = '';//$xml->ROW[0]->PL_POL;
        
        //echo "!";
        //var_dump($resXMLObj->Data->Bills);
        $billsData = $resXMLObj->Data->Bills;
        //var_dump($billsData);
        
        $arr_keys = ['ISXDOLG', 'OPLAT', 'SUBS', 'TARIF', 'SUMM_MONTH', 'SUMM_DOLG'];
        foreach ($billsData->Bill as $bill) {
            

            $billID = $bill->attributes()->ID;
            
            //var_dump($data['firm']);
            foreach ($bill->ServiceProviders->ServiceProvider as $ServiceProvider ){
                //var_dump($ServiceProvider );
                
                if(!isset($data['firm'][(string)$billID])){
                    //if (!array_key_exists((string)$billID, $data['firm'])) {
                    $ServiceProviderID = (string)$ServiceProvider->attributes()->ID;
                    $data['firm'][(string)$billID]['name'] = (string)$ServiceProvider->Title;
                    
                    $data['firm'][(string)$billID]['FIO'] = (string)$bill->PayerInfo->Credentials;
                    $data['firm'][(string)$billID]['TLF'] = '';//(string)$row->TLF;
                    $data['firm'][(string)$billID]['ABCOUNT'] = (string)$bill->PayerInfo->PACC;
                    
                    $data['firm'][(string)$billID]['debt_summ'] = (string)$ServiceProvider->DebtSumm;

                    $data['firm'][(string)$billID]['pay'] = 0;
                    foreach($bill->Payments->Payment as $payment)
                        $data['firm'][(string)$billID]['pay'] += (float)$payment->Summ;
                }
                
                //$arr_keys = ['ISXDOLG', 'OPLAT', 'SUBS', 'TARIF', 'SUMM_MONTH', 'SUMM_DOLG'];
                foreach ($ServiceProvider->Services->Service as $Service){
                    $firmData['NAME_PLAT'] = (string)$Service->Title;
                    $firmData['SUMM_MONTH'] = (string)$Service->AccruedSumm;//
                    $firmData['SUBS'] = '0,00';//
                    $data['data'][(string)$billID][] = $firmData;
                    
                    //$firmData['OPLAT'] = $data['firm'][(string)$ServiceProvider->attributes()->ID]['pay'];
                    //$firmData['ISXDOLG'] = (string)$ServiceProvider->DebtSumm;
                    
                    if (isset($Service->Counter)) {
                        $data['counter'] = 1;
                        foreach ($Service->Counter as $counter) {
                            $data['firm'][(string)$billID]['counter'][] = [
                            'COUNTER_NO' => '',// (string)$counter->COUNTER_NO,
                            'OLD_VALUE'  => (string)$counter->OLD_VALUE,
                            'TARIF'      => str_replace(".", ",", sprintf('%.2f', ((float)$counter->Tariff))),
                            'NAME_PLAT'  => (string)$Service->Title
                            ];
                        }
                    }
                    
                }
                
            }
        
            
            if ($row->NAIM_LG) {
                $data['firm'][(string)$row->CODE_FIRME]['lgoti'] = [
                'NAIM_LG'  => (string)$row->NAIM_LG,
                'PROC_LG'  => (string)$row->PROC_LG,
                'KOL_LGOT' => (string)$row->KOL_LGOT
                ];
            }
            if ($row->COUNTERS->COUNTERS_ITEM) {
                $data['counter'] = 1;
                foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                    $data['firm'][(string)$row->CODE_FIRME]['counter'][] = [
                    'COUNTER_NO' => (string)$counter->COUNTER_NO,
                    'OLD_VALUE'  => (string)$counter->OLD_VALUE,
                    'TARIF'      => str_replace(".", ",", sprintf('%.2f', ((float)$row->TARIF)/100)),
                    'NAME_PLAT'  => $this->getNamePlat($row->NAME_PLAT)
                    ];
                }
            }
        
            $data['firm'][(string)$row->CODE_FIRME]['FIO'] = (string)$row->FIO;
            $data['firm'][(string)$row->CODE_FIRME]['TLF'] = (string)$row->TLF;
            $data['firm'][(string)$row->CODE_FIRME]['ABCOUNT'] = (string)$row->ABCOUNT;
        
            foreach ($arr_keys as $arr_key) {
                $firmData[$arr_key] = ((int)$row->$arr_key != 0)
                ? str_replace(".", ",", sprintf('%.2f', ((float)$row->$arr_key) / 100))
                : '0,00';
            }
        
            //$firmData['NAME_PLAT']  = $this->getNamePlat($row->NAME_PLAT);
            //$firmData['TLF'] = (string)$row->TLF;
        
            //$data['data'][(string)$row->CODE_FIRME][] = $firmData;
        }
        
        //var_dump($data);
        return $data;
        
        
        
        
        //OLD METHOD
        $data = [];
        $xmlString = $this->getXML($this->debt_URL, $obj_id, $dateBegin);
        $xml = @new SimpleXMLElement($xmlString);
        $data['date'] = '01 ' . $this->months[date("n")]['ua'] . " " . date("Y");
        $data['curr_month'] = date("m");
        $data['curr_year'] = date("Y");
        $data['dateBegin'] = $dateBegin;
        if (isset($xml->ROW[0]->KOM_ERROR)) {
            $error = (string)$xml->ROW[0]->KOM_ERROR;
        }
    
        if ((!$is_filter) && (!empty($error))) {
            self::getPreviosMonth($previousMonth, $previousYear);
    
            $month2 = date("m", strtotime("01.".$previousMonth.".".$previousYear));
            $year2 = date("Y", strtotime("01.".$previousMonth.".".$previousYear));
            $dateBegin = "01.".$month2.".".$year2;
            $xmlString = $this->getXML($this->debt_URL, $obj_id, $dateBegin);
            $xml = @new SimpleXMLElement($xmlString);
            if (isset($xml->ROW[0]->KOM_ERROR)) {
                $error = (string)$xml->ROW[0]->KOM_ERROR;
            }
    
            $data['dateBegin'] = $dateBegin;
            $data['date'] = '01 ' . $this->months[$previousMonth]['ua'] . " " . $previousYear;
            $data['curr_month'] = date("m", strtotime("01".".".$previousMonth . "." . $previousYear));
            $data['curr_year'] = $previousYear;
        }
    
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
    
        $data['firm'] = [];
        $data['dbegin'] = date("d.m.y", strtotime($this->beginDate));
        $data['dend'] = date("d.m.y", strtotime($this->endDate));
    
        $debtBeginMonth = DateTime::createFromFormat('j.m.Y', $this->beginDate);
        $debtBeginMonth = strtotime('first day of previous month', date_timestamp_get($debtBeginMonth));
        $data['previous_date'] = date('d.m.Y', $debtBeginMonth);

        
        $data['begin_month'] = $this->monthsFullName[date('n', strtotime($this->beginDate))]['ua']['big'];
        $data['previous_month'] = $this->monthsFullName[date('n', $debtBeginMonth)]['ua']['big'];
        $data['counter'] = 0;
        $data['PEOPLE'] = $xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = $xml->ROW[0]->PL_OB;
        $data['PL_POL'] = $xml->ROW[0]->PL_POL;
    
        $data['TLF'] = $xml->ROW[0]->TLF;
        $arr_keys = ['ISXDOLG', 'OPLAT', 'SUBS', 'TARIF', 'SUMM_MONTH', 'SUMM_DOLG'];
    
        foreach ($xml->xpath("//ROW") as $row) {
            if ($firmName && (string)$row->CODE_FIRME != $firmName) {
                continue;
            }
    
            if (!array_key_exists((string)$row->CODE_FIRME, $data['firm'])) {
                $data['firm'][(string)$row->CODE_FIRME]['name'] = (string)$row->NAME_FIRME;
            }
            if ($row->NAIM_LG) {
                $data['firm'][(string)$row->CODE_FIRME]['lgoti'] = [
                'NAIM_LG'  => (string)$row->NAIM_LG,
                'PROC_LG'  => (string)$row->PROC_LG,
                'KOL_LGOT' => (string)$row->KOL_LGOT
                ];
            }
            if ($row->COUNTERS->COUNTERS_ITEM) {
                $data['counter'] = 1;
                foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                    $data['firm'][(string)$row->CODE_FIRME]['counter'][] = [
                    'COUNTER_NO' => (string)$counter->COUNTER_NO,
                    'OLD_VALUE'  => (string)$counter->OLD_VALUE,
                    'TARIF'      => str_replace(".", ",", sprintf('%.2f', ((float)$row->TARIF)/100)),
                    'NAME_PLAT'  => $this->getNamePlat($row->NAME_PLAT)
                    ];
                }
            }
    
            $data['firm'][(string)$row->CODE_FIRME]['FIO'] = (string)$row->FIO;
            $data['firm'][(string)$row->CODE_FIRME]['TLF'] = (string)$row->TLF;
            $data['firm'][(string)$row->CODE_FIRME]['ABCOUNT'] = (string)$row->ABCOUNT;
    
            foreach ($arr_keys as $arr_key) {
                $firmData[$arr_key] = ((int)$row->$arr_key != 0)
                ? str_replace(".", ",", sprintf('%.2f', ((float)$row->$arr_key) / 100))
                : '0,00';
            }
    
            $firmData['NAME_PLAT']  = $this->getNamePlat($row->NAME_PLAT);
            $firmData['TLF'] = (string)$row->TLF;
    
            $data['data'][(string)$row->CODE_FIRME][] = $firmData;
        }
    
        //var_dump($data);
        return $data;
    }
    
    public function getUniqueFirm($obj_id, $firmName = null, $dateBegin = null, $is_filter = false)
    {
        $data = [];
        $xmlString = $this->getXML($this->debt_URL, $obj_id, $dateBegin);
        $xml = @new SimpleXMLElement($xmlString);
        $data['date'] = '01 ' . $this->months[date("n")]['ua'] . " " . date("Y");
        $data['curr_month'] = date("m");
        $data['curr_year'] = date("Y");
        $data['dateBegin'] = $dateBegin;
        
        if (isset($xml->ROW[0]->KOM_ERROR)) {
            $error = (string)$xml->ROW[0]->KOM_ERROR;
            throw new Exception(ERROR_GETTING_DEBT);
        }

        $data['firm'] = [];
        $data['dbegin'] = date("d.m.y", strtotime($this->beginDate));
        $data['dend'] = date("d.m.y", strtotime($this->endDate));
        
        $debtBeginMonth = DateTime::createFromFormat('j.m.Y', $this->beginDate);
        $debtBeginMonth = strtotime('first day of previous month', date_timestamp_get($debtBeginMonth));
        $data['previous_date'] = date('d.m.Y', $debtBeginMonth);

        
        $data['begin_month'] = $this->monthsFullName[date('n', strtotime($this->beginDate))]['ua']['big'];
        $data['previous_month'] = $this->monthsFullName[date('n', $debtBeginMonth)]['ua']['big'];
        $data['counter'] = 0;
        $data['PEOPLE'] = $xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = $xml->ROW[0]->PL_OB;
        $data['PL_POL'] = $xml->ROW[0]->PL_POL;
            
        $data['TLF'] = $xml->ROW[0]->TLF;
        $arr_keys = ['ISXDOLG', 'OPLAT', 'SUBS', 'TARIF', 'SUMM_MONTH', 'SUMM_DOLG'];

        foreach ($xml->xpath("//ROW") as $row) {
            if ($firmName && (string)$row->CODE_FIRME != $firmName) {
                continue;
            }
            
            if (!array_key_exists((string)$row->CODE_FIRME, $data['firm'])) {
                $data['firm'][(string)$row->CODE_FIRME]['name'] = (string)$row->NAME_FIRME;
            }
            if ($row->NAIM_LG) {
                $data['firm'][(string)$row->CODE_FIRME]['lgoti'] = [
                    'NAIM_LG'  => (string)$row->NAIM_LG,
                    'PROC_LG'  => (string)$row->PROC_LG,
                    'KOL_LGOT' => (string)$row->KOL_LGOT
                ]; 
            }
            if ($row->COUNTERS->COUNTERS_ITEM) {
                $data['counter'] = 1;
                foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                    $data['firm'][(string)$row->CODE_FIRME]['counter'][] = [
                        'COUNTER_NO' => (string)$counter->COUNTER_NO,
                        'OLD_VALUE'  => (string)$counter->OLD_VALUE,
                        'TARIF'      => str_replace(".", ",", sprintf('%.2f', ((float)$row->TARIF)/100)),
                        'NAME_PLAT'  => $this->getNamePlat($row->NAME_PLAT)
                    ];
                }
            }
            
            $data['firm'][(string)$row->CODE_FIRME]['FIO'] = (string)$row->FIO;
            $data['firm'][(string)$row->CODE_FIRME]['TLF'] = (string)$row->TLF;
            $data['firm'][(string)$row->CODE_FIRME]['ABCOUNT'] = (string)$row->ABCOUNT;
            
            foreach ($arr_keys as $arr_key) {
                $firmData[$arr_key] = ((int)$row->$arr_key != 0)
                    ? str_replace(".", ",", sprintf('%.2f', ((float)$row->$arr_key) / 100))
                    : '0,00';
            }

            $firmData['NAME_PLAT']  = $this->getNamePlat($row->NAME_PLAT);
            $firmData['TLF'] = (string)$row->TLF;

            $data['data'][(string)$row->CODE_FIRME][] = $firmData;
        }

        return $data;
    }

    private function getNamePlat($string)
    {
        $string = str_replace('СЧЕТЧИК', 'ЛІЧИЛЬНИК', $string);
        $string = str_replace('счетчик', 'лічильник', $string);
        return $string;
    }
}
