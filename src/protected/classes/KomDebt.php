<?php

class KomDebt
{
    protected $cache = [];
    protected $komplat_URL = '/reports/rwservlet?report=/site/g_komoplat_un&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
    protected $debt_URL    = '/reports/rwservlet?report=/site/g_komdebt.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';

    private $months;
    private $monthsFullName;
    private $beginDate;
    private $endDate;

    public function __construct()
    {
        global $MONTHS, $MONTHS_NAME;
        $this->months = $MONTHS;
        $this->monthsFullName = $MONTHS_NAME;
    }
    
    private function getXML($url, $obj_id, $dateBegin = null)
    {
        try {
            $dateData = $this->getDatePeriod($dateBegin);
            $quertString  = "&dbegin=".$dateData['begin']."&dend=".$dateData['end'];
            
            if (strlen($obj_id) > 16) {
                if ($url == $this->debt_URL) {
                    $url = API_URL . "/reports/rwservlet?report=site/komdebt2.rep&cmdkey=gsity&destype=Cache&Desformat=xml&plat_code=" . $obj_id . $quertString;
                } else {
                    $url = API_URL . "/reports/rwservlet?report=site/komoplat.rep&cmdkey=gsity&destype=Cache&Desformat=xml&plat_code=" . $obj_id . $quertString;
                }
            } else {
                $url = API_URL . $url . $obj_id . $quertString;
            }

            if (defined('IS_ONLINE_REP')) {
                $url = str_replace('g_komdebt.rep', 'g_komdebt_online.rep', $url);
            }

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

    /**
     * получает данные об объекте, какие есть: платкод и id
     * и выбирает какой из этих параметров использовать
     * @param  integer $flat_id  ID квартиры в оракле
     * @param  string  $platcode платёжный код герца
     * @param  integer $city_id  ID города
     * @return integer | string
     */
    public static function getFlatIdOrPlatcode($flat_id, $platcode, $city_id)
    {
        if (strlen($platcode) > 16) {
            if (in_array($city_id, [Street::ODESSA_ID, Street::KIEV_ID])) {
                return $platcode;
            }
        }
    
        return $flat_id;
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
            $data['total_pay'] = 0;
            $data['PLAT_CODE'] = (isset($xml->ROW[0]->PLAT_CODE)) ? $xml->ROW[0]->PLAT_CODE . '' : null;

            // игнорируем определённый перечень ЖЕКов как поставщиков данных
            $without_recalc = require(PROTECTED_DIR . '/conf/without_recalc.php');
            $index = (in_array(floor($data['PLAT_CODE'] / 1000000), $without_recalc)) ? 1 : 0;

            $data['PEOPLE'] = (isset($xml->ROW[$index]->PEOPLE)) ? $xml->ROW[$index]->PEOPLE . '' : null;
            $data['PL_OB']  = (isset($xml->ROW[$index]->PL_OB))  ? $xml->ROW[$index]->PL_OB  . '' : null;
            $data['LGOTA']  = (isset($xml->ROW[$index]->LGOTA))  ? $xml->ROW[$index]->LGOTA  . '' : null;
            $data['PL_POL'] = (isset($xml->ROW[$index]->PL_POL)) ? $xml->ROW[$index]->PL_POL . '' : null;
            // Такие вещи, как PLAT_CODE и OUT_KEY доступны только в истории начислений
            
            $fullDept = 0;
            $data['list'] = [];

            foreach ($xml->xpath("//ROW") as $row) {
                $list = [];

                $tmp_keys = ['CODE_FIRME', 'CODE_PLAT', 'ID_PLAT', 'ABCOUNT', 'PLAT_CODE', 'DATE_D', 'FIO', 'TLF', 'R_COUNT', 'NAME_BANKS', 'ID_FIRME', 'SUMM_PLAT'];
                foreach ($tmp_keys as $tmp_key) {
                    $list[$tmp_key] = trim($row->$tmp_key . '');
                }

                $list['FIO'] = str_replace('ФИО НЕ УКАЗАНО', 'ПІБ НЕ ВКАЗАНО', $list['FIO']);

                $list['firm_name']    = str_replace('"', '&quot;', (string)$row->NAME_FIRME);
                $list['name_plat']    = $this->getNamePlat($row->NAME_PLAT);
                $list['NAME_BANKS']   = htmlspecialchars($list['NAME_BANKS'], ENT_QUOTES);
                $list['BANK_CODE']    = (string)$row->MFO;
                $list['DBEGIN_XML']   = date("Y-m-d", strtotime((string)$row->DBEGIN));
                $list['DEND_XML']     = date("Y-m-d", strtotime((string)$row->DEND));
                $list['ISXDOLG']      = ($row->ISXDOLG.'')      / 100;
                $list['OPLAT']        = ($row->OPLAT.'')        / 100;
                $list['SUMM_OBL_PAY'] = (empty($row->SUMM_OBL_PAY)) ? 0 : ($row->SUMM_OBL_PAY.'') / 100;
                
                $list['OPLAT']      = str_replace(".", ",", sprintf('%.2f', $list['OPLAT']));
                $list['SUMM_PLAT']  = (empty($list['SUMM_PLAT'])) ? 0 : $list['SUMM_PLAT'] / 100;
                $data['total_pay'] += $list['SUMM_PLAT'];

                $SUMM_MONTH = ((float)$row->SUMM_MONTH)/100;
                if ($SUMM_MONTH == 0) {
                    $list['SUMM_MONTH'] = '-';
                } else {
                    $list['SUMM_MONTH'] = sprintf('%.2f', $SUMM_MONTH);
                    $list['SUMM_MONTH'] = str_replace(".", ",", $list['SUMM_MONTH']);
                }

                if ($row->COUNTERS->COUNTERS_ITEM) {
                    
                    if (($list['SUMM_MONTH'] > 0) && ($row->SUMM_DOLG . '' == '0')) {
                        $row->SUMM_DOLG = $row->SUMM_MONTH . '';
                    }

                    $list['counterData']['FIRM_NAME'] = str_replace('"', '&quot;', (string)$row->NAME_FIRME);
                    $list['counterData']['CODE_FIRME'] = (int)$row->CODE_FIRME;
                    $list['counterData']['date'] = " 01.".date("n").".".date("y");
                    $list['counterData']['tarif'] = str_replace('.', ',', sprintf('%.3f',((float)str_replace(',', '.', $row->TARIF))/100));
                    $list['counterData']['real_tarif'] = (((float)str_replace(',', '.', $row->TARIF))/100);
                    $list['counterData']['original_tarif'] = $row->TARIF;
                    $list['counterData']['NAME_PLAT'] = $this->getNamePlat($row->NAME_PLAT);
                    $list['counterData']['NAIM_LG'] = (string)$row->NAIM_LG;
                    $list['counterData']['PROC_LG'] = (string)$row->PROC_LG;
                    $list['counterData']['KOL_LGOT'] = (string)$row->KOL_LGOT;
                    $list['counterData']['PEOPLE'] = (int)$row->PEOPLE;

                    foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                        $list['counterData']['counters'][] = [
                            'COUNTER_NO' => (int)$counter->COUNTER_NO,
                            'OLD_VALUE' => floatval(str_replace(",", ".", $counter->OLD_VALUE)),
                            'PRE_VALUE' => floatval(str_replace(",", ".", $counter->PRE_VALUE)),
                            'ABCOUNTER' => (string)$counter->ABCOUNTER,
                        ];
                    }
                }
                
                $debt = ((float)$row->SUMM_DOLG)/100;
                $debt -= $SUMM_MONTH;
                $debt = round($debt, 2);

                // сумма к оплате.
                // Если была переплата за прошлый месяц, то к оплате сумма за месяц (как будто нет переплаты)
                // Если есть счётчик и обязательный платёж, платим обязательный.
                $to_pay = ((float)$row->SUMM_DOLG)/100;
                $to_pay -= $list['SUMM_PLAT'];

                $list['SUMM_PLAT'] = str_replace(".", ",", sprintf('%.2f', $list['SUMM_PLAT']));
                $list['debt'] = sprintf('%.2f', $debt);
                $list['debt'] = str_replace(".", ",", $list['debt']);
                $list['to_pay'] = str_replace(".", ",", sprintf('%.2f', $to_pay));
                $list['counter'] = ($row->COUNTERS->COUNTERS_ITEM) ? 1 : 0;

                // if ($list['counter']) {
                //     $list['debt'] = "-";
                //     if ($list['SUMM_OBL_PAY'] >= 0) {
                //         $to_pay = $list['SUMM_OBL_PAY'];
                //     }
                // }
                if ($to_pay > 0) {
                    $fullDept += $to_pay * 100;
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

        if (!empty($data['list'][0]['DBEGIN_XML'])) {
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
                'NAME_FIRME'     => (string)$row->NAME_FIRME,
                'NAME_PLAT'      => $this->getNamePlat($row->NAME_PLAT),
                'SUMM'           => str_replace('.', ',', sprintf('%.2f',((float)$row->SUMM)/100)),
                'PDATE'          => date("d.m.y H:i:s", strtotime((string)$row->PDATE)),
                'ID_PLAT_KLIENT' => (isset($row->ID_PLAT_KLIENT)) ? $row->ID_PLAT_KLIENT . '' : null,
                'ABCOUNT'        => (string)$row->ABCOUNT,
                'DBEGIN'         => (string)$row->DBEGIN1,
                'DEND'           => (string)$row->DEND1,
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
        $have_data = false;
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
    
    public function getUniqueFirm($obj_id, $firmName = null, $dateBegin = null)
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
        $data['PEOPLE']  = (isset($xml->ROW[0]->PEOPLE)) ? $xml->ROW[0]->PEOPLE . '' : null;
        $data['PL_OB']   = (isset($xml->ROW[0]->PL_OB))  ? $xml->ROW[0]->PL_OB  . '' : null;
        $data['PL_POL']  = (isset($xml->ROW[0]->PL_POL)) ? $xml->ROW[0]->PL_POL . '' : null;
        $data['LGOTA']   = (isset($xml->ROW[0]->LGOTA))  ? $xml->ROW[0]->LGOTA  . '' : null;
            
        $data['TLF'] = $xml->ROW[0]->TLF;
        $arr_keys = ['ISXDOLG', 'OPLAT', 'SUBS', 'TARIF', 'SUMM_MONTH', 'SUMM_DOLG', 'SUMM_OBL_PAY'];

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
            $data['firm'][(string)$row->CODE_FIRME]['FIO'] = str_replace('ФИО НЕ УКАЗАНО', 'ПІБ НЕ ВКАЗАНО', $data['firm'][(string)$row->CODE_FIRME]['FIO']);
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
