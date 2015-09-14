<?php

class KomDebt
{
    const DEBTURL = '/reports/rwservlet?report=/site/g_komdebt.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
    const KOMPLATURL = '/reports/rwservlet?report=/site/g_komoplat.rep&cmdkey=gsity&destype=Cache&Desformat=xml&id_obj=';
    const ANSWERS_PATH = '/protected/conf/testing/';
    
    public $testing = false;
    protected $cache = array();

    private $months = array('1'=>'січня', '2'=>'лютого', '3'=>'березня', '4'=>'квiтня', '5'=>'травня', '6'=>'червня', '7'=>'липня', '8'=>'серпня', '9'=>'вересня', '10'=>'жовтня', '11'=>'листопада', '12'=>'грудня');
    private $monthsFullName = array('01'=>'Січень', '02'=>'Лютий', '03'=>'Березень', '04'=>'Квітень', '05'=>'Травень', '06'=>'Червень', '07'=>'Липень', '08'=>'Серпень', '09'=>'Вересень', '10'=>'Жовтень', '11'=>'Листопад', '12'=>'Грудень');
    private $beginDate;
    private $endDate;

    public function __construct()
    {
        $this->testing = !HAVE_ACCESS_TO_API;
    }
    
    private function getXML($url, $obj_id, $dateBegin = null)
    {
        if ($this->testing) {
            // в режиме тестирования мы не можем обращаться к API (скорее всего), так что берём шаблоны ответов
            if ($url == self::DEBTURL) {
                return file_get_contents(ROOT . self::ANSWERS_PATH . 'DEBTURL.xml');
            } else {
                return file_get_contents(ROOT . self::ANSWERS_PATH . 'KOMPLATURL.xml');
            }
        }

        $dateData = $this->getDatePeriod($dateBegin);
        $url = API_URL . $url . $obj_id . "&dbegin=" . $dateData['begin'] . "&dend=" . $dateData['end'];

        if (!isset($this->cache[md5($url)])) {
            $this->cache[md5($url)] = Http::fgets($url);
        }
        return $this->cache[md5($url)];
    }
    
    public function clearCache()
    {
        $this->cache = array();
    }

    public function getDebtSum($obj_id, $dateBegin = null)
    {
        $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        
        if (!empty($error)) {
            self::getPreviosMonth($previousMonth, $previousYear);
            $month2 = date("m", strtotime("01.".$previousMonth.".".$previousYear));
            $dateBegin = "01.".$month2.".".$previousYear;
            $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
            $xml = new SimpleXMLElement($xmlString);
            $error = (string)$xml->ROW[0]->KOM_ERROR;
        }
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $debt = 0;
        $have_data = false;

        foreach($xml->xpath("//ROW") as $row) {
            if((int)$row->SUMM_DOLG > 0 && !$row->COUNTERS->COUNTERS_ITEM) {
                $debt += (float)$row->SUMM_DOLG;
            }
            $have_data = true;
        }
        
        if(($debt == 0) && ($dateBegin == null) && !$have_data) {
            // maybe no data for this month
            $new_month = strftime('%m', strtotime('first day of previous month'));
            $new_year = ($new_month == '12')?(string)((int)date("Y") - 1):date("Y");
            $dateBegin = "1.".$new_month.".".$new_year;
            return $this->getDebtSum($obj_id, $dateBegin);
        }
        
        $debtStr = str_replace(".",",",sprintf('%.2f',((float)$debt)/100));
        
        return $debtStr;
    }
    
    private function getDatePeriod($dateBegin = null)
    {
        if(empty($dateBegin)) {
            $this->beginDate = $beginDate = "1.".date("m.Y");
        
            if(date("n") == 12) {
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
                    case 10:
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
        $temp_array = array();
      
        while (count($array)>0) {
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
    
    public function getData($obj_id, $dateBegin = null)
    {
        $data = array();
        $data['date'] = "01"." ".$this->months[date("n")]." ".date("Y");
        $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        
        if (!empty($error)) {
            self::getPreviosMonth($previousMonth, $previousYear);
            $month2 = date("m", strtotime("01-".$previousMonth."-".$previousYear));
            $data['date'] = "01"." ".$this->months[$previousMonth]." ".$previousYear;
            $dateBegin = "01.".$month2.".".$previousYear;
            $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
            $xml = new SimpleXMLElement($xmlString);
            $error = (string)$xml->ROW[0]->KOM_ERROR;
            
            $data['date'] = "01"." ".$this->months[$previousMonth]." ".$previousYear;
        }
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data['dbegin'] = $this->beginDate;
        $data['dend'] = $this->endDate;
        $data['PEOPLE'] = $xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = $xml->ROW[0]->PL_OB;
        $data['PL_POL'] = $xml->ROW[0]->PL_POL;
        
        $fullDept = 0;
        foreach ($xml->xpath("//ROW") as $row) {
            $list = array();
           
            $list['firm_name'] = str_replace('"', '&quot;', (string)$row->NAME_FIRME);
            $list['name_plat'] = (string)$row->NAME_PLAT;
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

            if ($row->COUNTERS->COUNTERS_ITEM) {
                $list['counterData']['FIRM_NAME'] = str_replace('"', '&quot;', (string)$row->NAME_FIRME);
                $list['counterData']['CODE_FIRME'] = (int)$row->CODE_FIRME;
                $list['counterData']['date'] = " 01.".date("n").".".date("y");
                $list['counterData']['tarif'] = str_replace(".",",",sprintf('%.2f',((float)$row->TARIF)/100));
                $list['counterData']['real_tarif'] = (float)($row->TARIF/100);
                $list['counterData']['NAME_PLAT'] = (string)$row->NAME_PLAT;
                $list['counterData']['NAIM_LG'] = (string)$row->NAIM_LG;
                $list['counterData']['PROC_LG'] = (string)$row->PROC_LG;
                $list['counterData']['KOL_LGOT'] = (string)$row->KOL_LGOT;
                $list['counterData']['PEOPLE'] = (int)$row->PEOPLE;

                foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                    $list['counterData']['counters'][] = array(
                        'COUNTER_NO' => (int)$counter->COUNTER_NO,
                        'OLD_VALUE' => (int)$counter->OLD_VALUE,
                        'ABCOUNTER' => (string)$counter->ABCOUNTER,
                    );
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
        
        if ((count($data['list']) == 0) && ($dateBegin == null)) {
            // maybe no data for this month
            $new_month = strftime('%m', strtotime('first day of previous month'));
            $new_year = ($new_month == '12')?(string)((int)date("Y") - 1):date("Y");
            $dateBegin = "1.".$new_month.".".$new_year;
            return $this->getData($obj_id, $dateBegin);
        }
        
        $data['full_dept'] = str_replace(".", ",", $data['full_dept']);
        
        return $data;
    }

    private static function getPreviosMonth(&$previousMonth, &$previousYear, $month = null)
    {
        if ($month == null) {
            $month = date("n");
        }
        
        if ($month == 1) {
            $previousMonth = 12;
            $previousYear = date("Y") - 1;
        } else {
            $previousMonth = $month - 1;
            $previousYear = date("Y");
        }
    }

    public function getHistoryBillData($obj_id, $dateBegin = null)
    {
        $xmlString = $this->getXML(self::KOMPLATURL, $obj_id, $dateBegin);
        $xmlString = str_replace("&nbsp;", "", $xmlString);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
    
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data = array();
        
        foreach ($xml->xpath("//ROW") as $row) {
            $data['bank'][(string)$row->PUNKT_ID]['NAMEOKP'] = (string)$row->NAMEOKP;
            $data['bank'][(string)$row->PUNKT_ID]['KASSA'] = (string)$row->KASSA;
            
            $dataArray = array(
                'NAME_FIRME'=>(string)$row->NAME_FIRME,
                'NAME_PLAT'=>(string)$row->NAME_PLAT,
                'SUMM'=>str_replace(".",",",sprintf('%.2f',((float)$row->SUMM)/100)),
                'PDATE'=>date("d.m.y H:i:s", strtotime((string)$row->PDATE)),
                'ABCOUNT'=>(string)$row->ABCOUNT,
                'DBEGIN'=>(string)$row->DBEGIN1,
                'DEND'=>(string)$row->DEND1
            );

            if ($row->CNTR->CNTR_ITEM) {
                $dataArray['counter'] = 1;
                $dataArray['OLD_VALUE'] = (string)$row->CNTR->CNTR_ITEM->OLD_VALUE;
                $dataArray['NEW_VALUE'] = (string)$row->CNTR->CNTR_ITEM->NEW_VALUE;
            }
        
            $data['bank'][(string)$row->PUNKT_ID]['data'][] = $dataArray;
        }
        return $data;
    }
    
    public function getPayOnThisMonth($obj_id, $dateBegin = null)
    {
        $xmlString = $this->getXML(self::KOMPLATURL, $obj_id, $dateBegin);
        $xmlString = str_replace("&nbsp;", "", $xmlString);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
            return false;
        }
        
        $summ = 0.0;
        foreach ($xml->xpath("//ROW") as $row) {
            $summ += (float)($row->SUMM)/100;
        }
        
        return str_replace(".", ",", sprintf('%.2f', $summ));
    }
    
    public function getGenerealData($obj_id)
    {
        $data = array();
        $xmlString = $this->getXML(self::DEBTURL, $obj_id);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        $data['date'] = "01"." ".$this->months[date("n")]." ".date("Y");
        
        if (!empty($error)) {
            self::getPreviosMonth($previousMonth, $previousYear);
            $month2 = date("m", strtotime("01-".$previousMonth."-".$previousYear));
            $data['date'] = "01"." ".$this->months[$previousMonth]." ".$previousYear;
            $dateBegin = "01.".$month2.".".$previousYear;
            $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
            $xml = new SimpleXMLElement($xmlString);
            $error = (string)$xml->ROW[0]->KOM_ERROR;
        }
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data['PEOPLE'] = $xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = $xml->ROW[0]->PL_OB;
        $data['PL_POL'] = $xml->ROW[0]->PL_POL;
        
        return $data;
    }
    
    public function getUniqueFirmName($obj_id, $dateBegin = null)
    {
        $data = array();
        $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        
        if (!empty($error)) {
            self::getPreviosMonth($previousMonth, $previousYear);
            $month2 = date("m", strtotime("01-".$previousMonth."-".$previousYear));
            $data['date'] = "01"." ".$this->months[$previousMonth]." ".$previousYear;
            $dateBegin = "01.".$month2.".".$previousYear;
            $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
            $xml = new SimpleXMLElement($xmlString);
            $error = (string)$xml->ROW[0]->KOM_ERROR;
        }
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data['firm'] = array();
        
        foreach ($xml->xpath("//ROW") as $row) {
            if (!array_key_exists((string)$row->CODE_FIRME, $data['firm'])) {
                $data['firm'][(string)$row->CODE_FIRME]['name'] = (string)$row->NAME_FIRME;
            }
        }
        
        return  $data['firm'];
    }
    
    public function haveDataToThisMounth($obj_id)
    {
        $xmlString = $this->getXML(self::DEBTURL, $obj_id, null);
        $xml = new SimpleXMLElement($xmlString);
        foreach ($xml->xpath("//ROW") as $row) {
            return true;
        }
        return false;
    }
    
    public function getUniqueFirm($obj_id, $firmName = null, $dateBegin = null, $is_filter = false)
    {
        $data = array();
        $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        $data['date'] = "01"." ".$this->months[date("n")]." ".date("Y");
        $data['curr_month'] = date("m");
        $data['curr_year'] = date("Y");
        $data['dateBegin'] = $dateBegin;
        
        if ((!$is_filter) && (!empty($error))) {
            self::getPreviosMonth($previousMonth, $previousYear);

            $month2 = date("m", strtotime("01.".$previousMonth.".".$previousYear));
            $year2 = date("Y", strtotime("01.".$previousMonth.".".$previousYear));
            $dateBegin = "01.".$month2.".".$year2;
            $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
            $xml = new SimpleXMLElement($xmlString);
            $error = (string)$xml->ROW[0]->KOM_ERROR;
                
            $data['dateBegin'] = $dateBegin;
            $data['date'] = "01"." ".$this->months[$previousMonth]." ".$previousYear;
            $data['curr_month'] = date("m", strtotime("01".".".$previousMonth.".".$previousYear));
            $data['curr_year'] = $previousYear;
        }
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data['firm'] = array();
        $data['dbegin'] = date("d.m.y", strtotime($this->beginDate));
        $data['dend'] = date("d.m.y", strtotime($this->endDate));
        
        $debtBeginMonth = date("n", strtotime($this->beginDate));
        self::getPreviosMonth($previousMonth, $previousYear, $debtBeginMonth);
        
        $data['previous_date'] = "01.".date("m", strtotime("01-".$previousMonth."-".date("Y"))).".".$previousYear;
        $data['begin_month'] = $this->monthsFullName[date("m", strtotime($this->beginDate))];
        $data['previous_month'] = $this->monthsFullName[date("m", strtotime("01-".$previousMonth."-".$previousYear))];
        $data['counter'] = 0;
        $data['PEOPLE'] = $xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = $xml->ROW[0]->PL_OB;
        $data['PL_POL'] = $xml->ROW[0]->PL_POL;
            
        $data['TLF'] = $xml->ROW[0]->TLF;

        foreach ($xml->xpath("//ROW") as $row) {
            if ($firmName && (string)$row->CODE_FIRME != $firmName) {
                continue;
            }
            
            if (!array_key_exists((string)$row->CODE_FIRME, $data['firm'])) {
                $data['firm'][(string)$row->CODE_FIRME]['name'] = (string)$row->NAME_FIRME;
            }
            if ($row->NAIM_LG) {
                $data['firm'][(string)$row->CODE_FIRME]['lgoti'] = array('NAIM_LG'=>(string)$row->NAIM_LG, 'PROC_LG'=>(string)$row->PROC_LG, 'KOL_LGOT'=>(string)$row->KOL_LGOT); 
            }
            if ($row->COUNTERS->COUNTERS_ITEM) {
                $data['counter'] = 1;
                foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                    $data['firm'][(string)$row->CODE_FIRME]['counter'][] = array('COUNTER_NO'=>(string)$counter->COUNTER_NO, 'OLD_VALUE'=>(string)$counter->OLD_VALUE, 'TARIF'=>str_replace(".",",",sprintf('%.2f',((float)$row->TARIF)/100)), 'NAME_PLAT'=>(string)$row->NAME_PLAT);
                }
            }
            
            $data['firm'][(string)$row->CODE_FIRME]['FIO'] = (string)$row->FIO;
            $data['firm'][(string)$row->CODE_FIRME]['TLF'] = (string)$row->TLF;
            $data['firm'][(string)$row->CODE_FIRME]['ABCOUNT'] = (string)$row->ABCOUNT;
            
            $firmData['ISXDOLG']    = ((int)$row->ISXDOLG != 0)    ? str_replace(".",",",sprintf('%.2f',((float)$row->ISXDOLG)/100))    : '0,00';
            $firmData['OPLAT']      = ((int)$row->OPLAT != 0)      ? str_replace(".",",",sprintf('%.2f',((float)$row->OPLAT)/100))      : '0,00';
            $firmData['SUBS']       = ((int)$row->SUBS != 0)       ? str_replace(".",",",sprintf('%.2f',((float)$row->SUBS)/100))       : '0,00';
            $firmData['TARIF']      = ((int)$row->TARIF != 0)      ? str_replace(".",",",sprintf('%.2f',((float)$row->TARIF)/100))      : '0,00';
            $firmData['SUMM_MONTH'] = ((int)$row->SUMM_MONTH != 0) ? str_replace(".",",",sprintf('%.2f',((float)$row->SUMM_MONTH)/100)) : '0,00';
            $firmData['SUMM_DOLG']  = ((int)$row->SUMM_DOLG != 0)  ? str_replace(".",",",sprintf('%.2f',((float)$row->SUMM_DOLG)/100))  : '0,00';
            
            $firmData['NAME_PLAT']  = (string)$row->NAME_PLAT;
            $firmData['TLF'] = (string)$row->TLF;

            $data['data'][(string)$row->CODE_FIRME][] = $firmData;
        }

        return $data;
    }
    
    public function getUniqueFirmPrint($obj_id, $firmName = null, $dateBegin = null)
    {
        $data = array();
        $xmlString = $this->getXML(self::DEBTURL, $obj_id, $dateBegin);
        $xml = new SimpleXMLElement($xmlString);
        $error = (string)$xml->ROW[0]->KOM_ERROR;
        
        if (!empty($error)) {
            throw new Exception(ERROR_GETTING_DEBT);
        }
        
        $data['firm'] = array();
        $data['date'] = "01"." ".$this->months[date("n")]." ".date("Y");
        $data['dbegin'] = date("d.m.y", strtotime($this->beginDate));
        $data['dend'] = date("d.m.y", strtotime($this->endDate));
        
        $debtBeginMonth = date("n", strtotime($this->beginDate));
        self::getPreviosMonth($previousMonth, $previousYear, $debtBeginMonth);
        
        $data['previous_date'] = "01.".date("m", strtotime("01-".$previousMonth2."-".date("Y"))).".".$previousYear2;
        $data['begin_month'] = $this->monthsFullName[date("m", strtotime($this->beginDate))];
        $data['previous_month'] = $this->monthsFullName[date("m", strtotime("01-".$previousMonth2."-".$previousYear2))];
        $data['FIO'] = (string)$xml->ROW[0]->FIO;
        // $data['ABCOUNT'] = (string)$xml->ROW[0]->ABCOUNT;
        $data['counter'] = 0;
        $data['PEOPLE'] = $xml->ROW[0]->PEOPLE;
        $data['PL_OB'] = $xml->ROW[0]->PL_OB;
        $data['PL_POL'] = $xml->ROW[0]->PL_POL;
        
        foreach ($xml->xpath("//ROW") as $row) {
            if ($firmName && (string)$row->CODE_FIRME != $firmName) {
                continue;
            }
            
            $data['firm'][(string)$row->CODE_FIRME]['ABCOUNT'] = (string)$row->ABCOUNT;
            $data['firm'][(string)$row->CODE_FIRME]['FIO'] = (string)$row->FIO;
            $data['firm'][(string)$row->CODE_FIRME]['TLF'] = (string)$row->TLF;

            if (!array_key_exists((string)$row->CODE_FIRME, $data['firm'])) {
                $data['firm'][(string)$row->CODE_FIRME]['name'] = (string)$row->NAME_FIRME;
            }
            
            if ($row->NAIM_LG) {
                $data['firm'][(string)$row->CODE_FIRME]['lgoti'] = array(
                    'NAIM_LG'=>(string)$row->NAIM_LG,
                    'PROC_LG'=>(string)$row->PROC_LG,
                    'KOL_LGOT'=>(string)$row->KOL_LGOT
                ); 
            }
            
            if ($row->COUNTERS->COUNTERS_ITEM) {
                $data['counter'] = 1;
                foreach ($row->COUNTERS->COUNTERS_ITEM as $counter) {
                    $data['firm'][(string)$row->CODE_FIRME][(string)$row->NAME_PLAT]['counter'][] = array(
                        'COUNTER_NO' => (string)$counter->COUNTER_NO,
                        'OLD_VALUE'  => (string)$counter->OLD_VALUE,
                        'TARIF'      => str_replace(".",",",sprintf('%.2f',((float)$row->TARIF)/100)),
                        'NAME_PLAT'  => (string)$row->NAME_PLAT
                    );
                }
            }
            
            $firmData['ISXDOLG']    = ((int)$row->ISXDOLG != 0)    ? str_replace(".",",",sprintf('%.2f',((float)$row->ISXDOLG)/100))    : '0,00';
            $firmData['OPLAT']      = ((int)$row->OPLAT != 0)      ? str_replace(".",",",sprintf('%.2f',((float)$row->OPLAT)/100))      : '0,00';
            $firmData['SUBS']       = ((int)$row->SUBS != 0)       ? str_replace(".",",",sprintf('%.2f',((float)$row->SUBS)/100))       : '0,00';
            $firmData['TARIF']      = ((int)$row->TARIF != 0)      ? str_replace(".",",",sprintf('%.2f',((float)$row->TARIF)/100))      : '0,00';
            $firmData['SUMM_MONTH'] = ((int)$row->SUMM_MONTH != 0) ? str_replace(".",",",sprintf('%.2f',((float)$row->SUMM_MONTH)/100)) : '0,00';
            $firmData['SUMM_DOLG']  = ((int)$row->SUMM_DOLG != 0)  ? str_replace(".",",",sprintf('%.2f',((float)$row->SUMM_DOLG)/100))  : '0,00';
            
            $firmData['NAME_PLAT'] = (string)$row->NAME_PLAT;
            $data['data'][(string)$row->CODE_FIRME][] = $firmData;
        }
        
        return $data;
    }
}
