<?php
	function generateCode($length = 8, $possible = '012346789abcdefghijkmnopqrtvwxyz')
	{
		$string = '';
		$maxlength = strlen($possible);
		$i = 0;

		while ($i < $length) {
			$char = substr($possible, mt_rand(0, $maxlength-1), 1);
			$string .= $char;
			$i++;
		}

		return $string;
	}

	function getNumericPostfix($n, $form1, $form2, $form5)
	{
		$n = abs($n) % 100;
		$n1 = $n % 10;
		if ($n > 10 && $n < 20) {
			return $form5;
		}
		if ($n1 > 1 && $n1 < 5) {
			return $form2;
		}
		if ($n1 == 1) {
			return $form1;
		}
		return $form5;
	}

	function arrayToXml($array)
	{
		$xml = '';
		foreach ($array as $key=>$value) {
			if (is_array($value)) {
				$xml .= "<" . $key . ">\n";
				$xml .= arrayToXml($value);
				$xml .= "</" . $key . ">\n";
			} else {
				$value = str_replace("&", '\&', htmlspecialchars($value));
				$xml .= "<" . $key . ">";
				$xml .= $value . "";
				$xml .= "</" . $key . ">\n";
			}
		}
		
		return $xml;
	 }

	function toXML($array, $success = 'true')
	{
		$xml = '';
		$xml .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$xml .= '<records success="'.$success.'">'."\n" ;
		$xml .= arrayToXml($array);
		$xml .= '</records>'."\n";
		 
		return $xml;
	}

	function translitIt($str)
	{
		$tr = array(
			'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Ґ'=>'G',
			'Д'=>'D','Е'=>'E','Є'=>'YE','Ж'=>'J','З'=>'Z','И'=>'I','І'=>'I','Ї'=>'YI',
			'Й'=>'Y','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N',
			'О'=>'O','П'=>'P','Р'=>'R','С'=>'S','Т'=>'T',
			'У'=>'U','Ф'=>'F','Х'=>'H','Ц'=>'TS','Ч'=>'CH',
			'Ш'=>'SH','Щ'=>'SCH','Ъ'=>'','Ы'=>'YI','Ь'=>'',
			'Э'=>'E','Ю'=>'YU','Я'=>'YA','а'=>'a','б'=>'b',
			'в'=>'v','г'=>'g','ґ'=>'g','д'=>'d','е'=>'e','є'=>'ye','ж'=>'j',
			'з'=>'z','и'=>'i','і'=>'i','ї'=>'yi','й'=>'y','к'=>'k','л'=>'l',
			'м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r',
			'с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h',
			'ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'',
			'ы'=>'yi','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
		);
		return strtr($str, $tr);
	}	
	
	function composeUrlKey($str)
	{
		$res = trim($str);
		$res = translitIt($res);
		$res = strtolower($res);
		for ($i = 0; $i < strlen($res); $i++) {
			if (!preg_match("([0-9a-z\-]+)", $res[$i])) {
				$res[$i] = "-";
			}
		}
		$result = "";
		for ($i = 0; $i < strlen($res); $i++) {
			if (!($res[$i] == "-" && $res[$i+1] == "-")) {
				$result .= $res[$i];
			}
		}
		
		return $result;
	}

	function getMenu($type)
	{
		$menu_items = PDO_DB::table_list(DB_TBL_MENUS, "`type`='$type' AND `is_active`=1", "`pos` ASC");
		$menu = array();
		
		for ($i=0; $i<count($menu_items); $i++) {
			if ($menu_items[$i]['idp'] == 0) {
				$submenu = array();
				for ($j=0; $j<count($menu_items); $j++) {
					if ($menu_items[$j]['idp'] == $menu_items[$i]['id']) {
						$submenu[] = $menu_items[$j];
					}
				}
				$menu[] = $menu_items[$i];
				$menu[count($menu)-1]['submenu'] = $submenu;
			}
		}

		return $menu;
	}
