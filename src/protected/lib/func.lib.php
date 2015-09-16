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
        
        return trim($result, '-');
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

    function insertPagination($pagesCount, $currentPage, $url_for_paging, $item_on_page = 50)
    {
        if ($pagesCount == 0) {
            return;
        }
        $pages = array();

        if ($pagesCount > 20) {
            if ($currentPage > 6) {
                $pages = array(0, 1, 2, 3, 'points');
                $start_page = ($pagesCount - $currentPage > 7)?$currentPage-3:$pagesCount-11;
                
                for ($i=$start_page; $i<$start_page+10; $i++) {
                    $pages[] = $i;
                }
                
                $last_page = $pages[count($pages)-1];
                
                if ($last_page < $pagesCount-5) {
                    $pages[] = 'points';
                }
                
                for ($i=$pagesCount-4; $i<$pagesCount; $i++) {
                    if ($i > $last_page) {
                        $pages[] = $i;
                    }
                }
            } else {
                for ($i=0; $i<$currentPage+8; $i++) {
                    $pages[] = $i;
                }
                
                $pages[] = 'points';
                
                for ($i=$pagesCount-4; $i<$pagesCount; $i++) {
                    $pages[] = $i;
                }
            }
        } else {
            for ($i=0; $i<$pagesCount; $i++) {
                $pages[] = $i;
            }
        }
        $have_prev = ($currentPage > 0);
        $have_next = ($currentPage < $pagesCount - 1);
        
        if ($pagesCount > 1) {
            ?>
            <a class="first <?= $have_prev ? '' : 'disabled'; ?>" <?= $have_prev ? "href=\"$url_for_paging\"" : ''; ?>></a>
            <a class="prev  <?= $have_prev ? '' : 'disabled'; ?>" <?= $have_prev ? 'href="'.($url_for_paging . ($currentPage)).'/"' : ''; ?>></a>
            <?php
                for ($i=0; $i<count($pages); $i++) {
                    if (($pages[$i] !== 'points') && ($currentPage == $pages[$i])) {
                        ?> <a class="current"><?= $pages[$i] + 1; ?></a><?php
                    } else {
                        if ($pages[$i] === 0) {
                            ?> <a href="<?= $url_for_paging; ?>">1</a><?php
                        } elseif ($pages[$i] !== 'points') {
                            ?> <a href="<?= $url_for_paging . ($pages[$i] + 1); ?>/"><?= $pages[$i]+1; ?></a><?php
                        } else {
                            ?> <a class="points" onclick="return false;">...</a> <?php
                        }
                    }
                }
            ?>
            <a class="next <?= $have_next ? '' : 'disabled'; ?>" <?= $have_next ? "href=\"$url_for_paging" . ($currentPage + 2) . '/"' : ''; ?>></a>
            <a class="last <?= $have_next ? '' : 'disabled'; ?>" <?= $have_next ? 'href="'.$url_for_paging . $pagesCount . '/"' : ''; ?>></a>
            <?php
        }
    }

    function getTextVariableValueByName($name)
    {
        $pdo = PDO_DB::getPDO();
        $stm = $pdo->prepare("SELECT `text` FROM `".TABLE_PREFIX."text` WHERE `variable`=? LIMIT 1");
        $stm->execute([$name]);
        $record = $stm->fetchColumn();

        if ($record === false) {
            return null;
        }

        return $record;
    }

    function getUkraineDate($format, $timestamp)
    {
        global $MONTHS;
        $month = $MONTHS[date('n', $timestamp)]['ua'];
        $format = str_replace(['n', 'm'], [$month, $month], $format);
        return date($format, $timestamp);
    }
