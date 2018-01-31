<?php

    use cri2net\php_pdo_db\PDO_DB;
    
    $__is_email_mode = isset($_GET['email_mode']);
    $__is_text_mode = isset($_GET['text_mode']);
    $__img_path = BASE_URL . '/assets/pic/email/';
    $_text_color = 'color:#01b671;';

    // тут отображаем счёт для текущего авторизованного пользователя, если в ссылке не передан другой id
    // то есть на сайте ссылки сюда смогут быть без user_id.

    Authorization::check_login();

    if (Authorization::isLogin()) {
        $house = Flat::getUserFlatById($_GET['f'], $__userData['id']);
    }

    if (!Authorization::isLogin() || !$house || ($house['user_id'] != $__userData['id'])) {
        header("HTTP/1.1 404 Not Found");
        header("Location: https://cks.com.ua/");
        exit();
    }

    $info_block = PDO_DB::first(TABLE_PREFIX . 'text', "variable='INVOICE_INFO_BLOCK'");

    $hash1 = Authorization::get_auth_hash1($__userData['id']);
    $hash2 = Authorization::get_auth_hash2($__userData['id'], $hash1);
    $debt = new KomDebt();

    $paybill_link   = EXT_BASE_URL . '/cabinet/objects/' . $house['id'] . '/bill/?uid=' . $__userData['id'] . '&hash2=' . $hash2;
    $online_version = BASE_URL . '/invoice/?uid=' . $__userData['id'] . '&f=' . $house['id'] . '&hash2=' . $hash2;

    $address = Flat::getAddressString($house['flat_id'], $address_detail);
    
    $flat_number = '';
    if ($address_detail['flat'] != 0) {
        $flat_number = "квартира " . $address_detail['flat'];
    }

    $username = htmlspecialchars(trim("{$__userData['name']} {$__userData['fathername']}"));
    if ($username) {
        $username = ", $username!";
    }
    $say_good_day = 'Доброго дня' . $username;

    if ($__is_text_mode) {
        header("Content-type: text/plain; charset=UTF-8");

        if ($address_detail['flat']) {
            $_flat_number = ', квартира ' . $address_detail['flat'];
        }

        if ($info_block['text']) {
            $info_block_plain_text = html_entity_decode(strip_tags($info_block['text'])) . "\r\n";
        } else {
            $info_block_plain_text = '';
        }

        echo $say_good_day, "\r\n",
             'Доступний рахунок на сплату ЖКП для Київ, ', trim($house['street_name_full']), ', ', $address_detail['house'], $_flat_number, "\r\n",
             'Ви маєте можливість сплатити за комунальні послуги прямо зараз на сайті КК ЦКС або роздрукувати рахунок та сплатити у найближчій касі банку.', "\r\n\r\n",
             "Сплатити на сайті КК ЦКС: $paybill_link\r\n\r\n",
             $info_block_plain_text,
             "Онлайн версія листа: $online_version\r\n";
        exit();
    }

    $depth = ($__is_email_mode) ? 10 : 0;
    if ($__is_email_mode) {
        define('IS_ONLINE_REP', true);
    }
    $plat_code = KomDebt::getFlatIdOrPlatcode($house['flat_id'], $house['plat_code'], $house['city_id']);
    $debtData = $debt->getData($plat_code, null, $depth);
    if (empty($debtData['list']) && $__is_email_mode) {
        exit();
    }

    $dateBegin = date('1.m.Y', $debtData['timestamp']);
    $house['debt_sum'] = $debtData['full_dept'];

    $this_year = substr($debtData['dbegin'], strlen($debtData['dbegin'])-4);
    $this_month = (int)substr($debtData['dbegin'], strlen($debtData['dbegin'])-7, 2);
    $house['in_this_month'] = $MONTHS_WHEN[(int)$this_month];
    $house['date'] = '1 ' . $MONTHS[$this_month]['ua'] . ' ' . $this_year;


    $oplat = $debt->getPayOnThisMonth($plat_code, $dateBegin);
    $recalcData = $debt->getReCalc($house['flat_id'], date('01.m.Y', $debtData['timestamp'] + 86400 * 35));
    $have_recalc = !empty($recalcData);

    $tmp_oplat = (double)str_replace(',', '.', $oplat);
    $tmp_debt_summ = (double)str_replace(',', '.', $house['debt_sum']);
    $house['payed'] = ($tmp_oplat >= $tmp_debt_summ);
    
    $prev_month_name  = $MONTHS_NAME[date('n', strtotime('previous month'))]['ua']['small'];
    $prev_month_name_when  = $MONTHS_WHEN[date('n', strtotime('previous month'))]['ua'];
    $this_month_short = $MONTHS_NAME[date('n')]['ua']['short'];

    $_ff = 'font-family:Ubuntu, Arial, Times, Georgia;';
    $_table_attr = 'width="100%" cellspacing="0" cellpadding="0" border="0"';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html lang="uk">
<head>
<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
    if ($__is_email_mode) {
        ?><title></title><?php
    } else {
        ?><title>Рахунок на сплату ЖКП для <?= htmlspecialchars(trim($house['street_name_full'])); ?>, <?= htmlspecialchars($address_detail['house']); ?> <?= htmlspecialchars($flat_number); ?></title><?php
    }
?>
<!--[if (gte mso 9)|(IE)]>
<style type="text/css">
    table {border-collapse: collapse;}
</style>
<![endif]-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&subset=latin,cyrillic">
<?php
    if (!$__is_email_mode) {
        require_once(ROOT . '/protected/scripts/google-analytics.php');
    }
?>
</head>
<body style="margin:0; padding:0; min-width:730px; width:100%; <?= $_ff; ?> font-size:12px;">
<?php
    if ($__is_email_mode) {
        ?>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&subset=latin,cyrillic">
        <style>
            @import url(https://fonts.googleapis.com/css?family=Ubuntu:400,700,400italic&subset=latin,cyrillic);

            @font-face {
                font-family: 'Ubuntu';
                font-style: normal;
                font-weight: 400;
                src: local('Ubuntu'), url(https://fonts.gstatic.com/s/ubuntu/v7/qwCYE106NwHxtQIaygqBug.ttf) format('truetype');
            }
            @font-face {
                font-family: 'Ubuntu';
                font-style: normal;
                font-weight: 700;
                src: local('Ubuntu Bold'), local('Ubuntu-Bold'), url(https://fonts.gstatic.com/s/ubuntu/v7/4z2U46_RRLOfkoHsWJG3v6CWcynf_cDxXwCLxiixG1c.ttf) format('truetype');
            }
            @font-face {
                font-family: 'Ubuntu';
                font-style: italic;
                font-weight: 400;
                src: local('Ubuntu Italic'), local('Ubuntu-Italic'), url(https://fonts.gstatic.com/s/ubuntu/v7/hBxMw5gGdaT2CoPdCGcAqPesZW2xOQ-xsNqO47m55DA.ttf) format('truetype');
            }
        </style>
        <?php
    }
?>
<table <?= $_table_attr; ?>><tbody><tr><td>
    <table width="100%" background="<?= $__img_path; ?>top-line.png" style="background-position:top; background-repeat:repeat-x; background-color:#ffffff;" cellspacing="0" cellpadding="10" border="0"><tbody>
        <tr>
            <td height="34" width="140" style="vertical-align:top; padding-top:6px; padding-right:0px; padding-bottom:0px; padding-left:20px;" align="left"><strong><a style="text-decoration:none; font-weight:bold; <?= $_ff; ?> color:#004444; font-size:14px;" href="tel:+380442474040">(044) 247 40 40</a></strong></td>
            <td height="40" style="padding:0;" align="center">
                <a href="<?= htmlspecialchars($online_version); ?>" target="_blank">
                    <img width="160" height="40" hspace="0" vspace="0" border="0" src="<?= $__img_path; ?>online-version.png" alt="online-version">
                </a>
            </td>
            <td height="34" width="140" style="vertical-align:top; padding-top:6px; padding-right:20px; padding-bottom:0px; padding-left:0px;" align="right"><a target="_blank" style="<?= $_text_color; ?> font-size:14px; line-height:14px; font-weight:bold; <?= $_ff; ?>" href="https://cks.com.ua/">cks.com.ua</a></td>
        </tr>
    </tbody></table>
    <table <?= $_table_attr; ?>><tbody>
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
            <td width="24">&nbsp;</td>
            <td align="center"><table width="308" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr><td height="40" colspan="3" align="center" valign="top" style="vertical-align:top; <?= $_text_color; ?> <?= $_ff; ?> font-weight:bold; font-size:24px; line-height:20px;">Рахунок-повідомлення</td></tr>
                    <tr>
                        <td width="137" height="32"><table background="<?= $__img_path; ?>date-left.png" <?= $_table_attr; ?>>
                            <tbody><tr>
                                <td width="137" align="center" height="32" style="color:#444444; <?= $_ff; ?> font-size:14px; line-height:14px; font-weight:normal;">за <b><?= $MONTHS_NAME[date('n', $debtData['timestamp'])]['ua']['small'] . date(' Y', $debtData['timestamp']); ?></b></td>
                            </tr></tbody>
                        </table></td>
                        <td style="font-size:14px; line-height:14px;" width="44">&nbsp;</td>
                        <td width="137" height="32"><table background="<?= $__img_path; ?>date-right.png" <?= $_table_attr; ?>>
                            <tbody><tr>
                                <td width="137" align="center" height="32" style="color:#444444; <?= $_ff; ?> font-size:14px; line-height:14px; font-weight:normal;">від <b><?= date('d ') . $this_month_short . date(' Y'); ?></b></td>
                            </tr></tbody>
                        </table></td>
                    </tr>
                </tbody>
            </table></td>
            <td width="20">&nbsp;</td>
        </tr>
    </tbody></table>
    <table <?= $_table_attr; ?>>
        <tbody><tr><td style="padding:0px; line-height:23px;" height="23">&nbsp;</td></tr></tbody>
    </table>
    <table background="<?= $__img_path; ?>shadow-top.png" <?= $_table_attr; ?>>
        <tbody><tr><td style="padding:0px; line-height:10px;" height="10">&nbsp;</td></tr></tbody>
    </table>
    <table <?= $_table_attr; ?>><tbody><tr>
        <td width="20" style="line-height:12px;">&nbsp;</td>
        <td><table <?= $_table_attr; ?>><tbody><tr>
            <td valign="top" width="14" style="padding-top:3px;"><table background="<?= $__img_path; ?>marker.png" <?= $_table_attr; ?>>
                <tbody><tr><td style="padding:0px; line-height:20px;" height="20">&nbsp;</td></tr></tbody>
            </table></td>
            <td valign="top" align="left" style="padding-left:13px; <?= $_text_color; ?> <?= $_ff; ?> line-height:20px; font-size:14px;">
                <?= $address_detail['city']; ?><br>
                <span style="<?= $_text_color; ?> <?= $_ff; ?> line-height:20px; font-size:18px;"><?= htmlspecialchars(trim($house['street_name_full'])); ?>, <?= htmlspecialchars($address_detail['house']); ?></span> <br>
                <?= htmlspecialchars($flat_number); ?>
                <?php
                    if (isset($debtData['LGOTA']) && $debtData['LGOTA']) {
                        ?>
                        <br>Пільги: <?= $debtData['LGOTA']; ?>
                        <?php
                    }
                ?>
            </td>
        </tr></tbody></table></td>
        <td style="padding-top:14px; padding-bottom:14px; padding-right:8px;"><table <?= $_table_attr; ?>><tbody>
            <tr>
                <td align="right" style="<?= $_ff; ?> <?= $_text_color; ?> font-size:14px; line-height:18px; padding-right:15px;">Кількість прооживаючих:</td>
                <td align="left" width="55" style="<?= $_ff; ?> <?= $_text_color; ?> font-size:14px; line-height:18px;"><b><?= $debtData['PEOPLE']; ?></b></td>
            </tr>
            <tr>
                <td align="right" style="<?= $_ff; ?> <?= $_text_color; ?> font-size:14px; line-height:18px; padding-right:15px;">Загальна площа:</td>
                <td align="left" width="55" style="<?= $_ff; ?> <?= $_text_color; ?> font-size:14px; line-height:18px;"><b><?= $debtData['PL_OB']; ?></b> м<sup style="font-size:10px; <?= $_ff; ?> <?= $_text_color; ?> position:relative; top:-3px;">2</sup></td>
            </tr>
            <tr>
                <td align="right" style="<?= $_ff; ?> <?= $_text_color; ?> font-size:14px; line-height:18px; padding-right:15px;">Опалювальна площа:</td>
                <td align="left" width="55" style="<?= $_ff; ?> <?= $_text_color; ?> font-size:14px; line-height:18px;"><b><?= $debtData['PL_POL']; ?></b> м<sup style="font-size:10px; <?= $_ff; ?> <?= $_text_color; ?> position:relative; top:-3px;">2</sup></td>
            </tr>
        </tbody></table></td>
    </tr></tbody></table>
    <table background="<?= $__img_path; ?>shadow-bottom.png" <?= $_table_attr; ?>>
        <tbody><tr><td style="padding:0px; line-height:10px;" height="10">&nbsp;</td></tr></tbody>
    </table>
    <table width="100%" cellspacing="0" cellpadding="18" border="0"><tbody><tr>
        <td style="line-height:24px; <?= $_ff; ?> font-size:14px; color:#000000;" height="10">
            <span style="line-height:38px; font-size:18px; <?= $_ff; ?> font-weight:bold;"><?= $say_good_day; ?></span><br>
            Ви маєте можливість сплатити за комунальні послуги прямо зараз на сайті КК ЦКС.
            <?php
                if ($info_block['text']) {
                    echo '<br><br>', $info_block['text'];
                }
            ?>
        </td>
    </tr></tbody></table>

    <table <?= $_table_attr; ?>><tbody><tr>
        <td style="padding:0px; line-height:10px;" width="18" height="10">&nbsp;</td>
        <td width="10" style="padding:0px;" height="10">
            <table background="<?= $__img_path; ?>table-top-left.png" <?= $_table_attr; ?>><tbody><tr>
                <td style="padding:0px; line-height:10px;" height="10">&nbsp;</td>
            </tr></tbody></table>
        </td>
        <td bgcolor="#eeeeee" style="background-color:#eeeeee; padding:0px; line-height:10px;" height="10">&nbsp;</td>
        <td width="10" style="padding:0px;" height="10">
            <table background="<?= $__img_path; ?>table-top-right.png" <?= $_table_attr; ?>><tbody><tr>
                <td style="padding:0px; line-height:10px;" height="10">&nbsp;</td>
            </tr></tbody></table>
        </td>
        <td style="padding:0px; line-height:10px;" width="18" height="10">&nbsp;</td>
    </tr></tbody></table>
    <table <?= $_table_attr; ?>><tbody>
        <tr>
            <td style="padding:0px; line-height:32px;" width="18">&nbsp;</td>
            <td align="right" bgcolor="#eeeeee" style="background-color:#eeeeee; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px;" width="30" height="30" valign="top">№</td>
            <td align="left" bgcolor="#eeeeee" style="background-color:#eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px; padding-left:20px;" height="30" valign="top">Послуга / одержувач коштів</td>
            <td align="left" bgcolor="#eeeeee" style="background-color:#eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px;" height="30" valign="top">Заборг. / переплата<br /><span style="font-weight:normal; color:#000000; <?= $_ff; ?> font-size:12px;"> на 01.<?= date('m', strtotime('previous month')); ?>, грн</span>&nbsp;&nbsp;</td>
            <td align="left" bgcolor="#eeeeee" style="background-color:#eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px;" height="30" valign="top">Тариф, грн&nbsp;&nbsp;</td>
            <td align="left" bgcolor="#eeeeee" style="background-color:#eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px;" height="30" valign="top">Нараховано<span style="font-weight:normal; color:#000000; <?= $_ff; ?> font-size:12px;"> <br /> за <?= $prev_month_name; ?>, грн *</span>&nbsp;&nbsp;</td>
            <td align="left" bgcolor="#eeeeee" style="background-color:#eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px;" height="30" valign="top">До сплати<span style="font-weight:normal; color:#000000; <?= $_ff; ?> font-size:12px;"> <br /> за <?= $prev_month_name; ?>, грн</span></td>
            <td align="right" bgcolor="#eeeeee" style="background-color:#eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; font-weight:bold; line-height:18px; padding-bottom:2px; padding-right:30px;" height="30" valign="top">Сплачено<span style="font-weight:normal; color:#000000; <?= $_ff; ?> font-size:12px;"> <br /> у <?= $prev_month_name_when; ?>, грн **</span>&nbsp;&nbsp;</td>
            <td style="padding:0px; line-height:10px;" width="18">&nbsp;</td>
        </tr>
        <?php

            $number = 0;

            foreach ($debtData['list'] as $key => $debtData_item) {
                $number++;
                $rasp = [];
                $bg_color = ($number % 2 == 0) ? '#eeeeee' : '#ffffff';
                
                $have_counters = (isset($debtData_item['counterData']['counters']) && (count($debtData_item['counterData']['counters']) > 0));

                if (isset($debtData_item['counterData']['tarif'])) {
                    $tarif = explode(',', $debtData_item['counterData']['tarif']);
                } elseif (isset($debtData_item['TARIF']) && $debtData_item['TARIF'] != '0,00') {
                    $tarif = explode(',', $debtData_item['TARIF']);
                } else {
                    $tarif = '—';
                }

                if (is_array($tarif)) {
                    $tarif = $tarif[0] . '<span style="font-size:12px;">,'. $tarif[1] .'</span>';
                }

                $debtData_item['SUMM_MONTH'] = (isset($debtData_item['SUMM_MONTH'])) ? str_replace(',', '.', $debtData_item['SUMM_MONTH']) : '0,00';
                $summ_month = str_replace(".", ",", sprintf('%.2f', $debtData_item['SUMM_MONTH']));
                $summ_month = explode(',', $summ_month);
                $summ_month = $summ_month[0] . '<span style="font-size:12px;">,'. $summ_month[1] .'</span>';
                if ($have_counters && ($debtData_item['SUMM_MONTH'] == '0,00')) {
                    $summ_month = '—';
                }

                $debtData_item['OPLAT'] = (isset($debtData_item['OPLAT'])) ? $debtData_item['OPLAT'] : '0,00';
                $oplat = explode(',', $debtData_item['OPLAT']);
                $oplat = $oplat[0] . '<span style="font-size:12px;">,'. $oplat[1] .'</span>';

                $debtData_item['ISXDOLG'] = (isset($debtData_item['ISXDOLG'])) ? str_replace(',', '.', $debtData_item['ISXDOLG']) : '0,00';
                $prev_month_debt = str_replace(".", ",", sprintf('%.2f', $debtData_item['ISXDOLG']));
                $prev_month_debt = explode(',', $prev_month_debt);
                $prev_month_debt = $prev_month_debt[0] . '<span style="font-size:12px;">,'. $prev_month_debt[1] .'</span>';

                $to_pay = explode(',', $debtData_item['to_pay']);
                if (is_array($to_pay)) {
                    $to_pay = $to_pay[0] . '<span style="font-size:12px;">,'. $to_pay[1] .'</span>';
                }

                ?>
                <tr>
                    <td style="padding:0px; line-height:10px;" width="18">&nbsp;</td>
                    <td bgcolor="<?= $bg_color; ?>" align="right" style="border-left:1px solid #eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-bottom:2px;" height="30"><?= $number; ?></td>
                    <td bgcolor="<?= $bg_color; ?>" align="left" style="color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-top:9px; padding-bottom:11px; padding-left:20px;" height="30">
                        <?= $debtData_item['name_plat']; ?> <br>
                        <span style="color:#888888; line-height:18px; <?= $_ff; ?> font-size:12px;"><?= $debtData_item['firm_name']; ?> (о/р <?= htmlspecialchars($debtData_item['ABCOUNT']); ?>)</span> <br>
                        <?php
                            if ($have_counters) {
                                    
                                for ($i=0; $i < count($debtData_item['counterData']['counters']); $i++) {
                                    $str_counter_number = (count($debtData_item['counterData']['counters']) == 1)
                                        ? ':'
                                        : (' № ' . $debtData_item['counterData']['counters'][$i]['COUNTER_NO'] . ':');
                                    
                                    $str = 'Попередні показання лічильника' . $str_counter_number . ' <b style="font-weight:bold;">'. $debtData_item['counterData']['counters'][$i]['OLD_VALUE'] . '</b>';
                                    
                                    ?>
                                    <span style="color:#888888; line-height:18px; <?= $_ff; ?> font-size:12px;"><?= $str; ?></span> <br>
                                    <?php
                                }

                            }
                        ?>
                        <?= htmlspecialchars($debtData_item['FIO']); ?>
                    </td>
                    <td bgcolor="<?= $bg_color; ?>" align="left" style="color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-bottom:2px;" height="30"><?= $prev_month_debt; ?></td>
                    <td bgcolor="<?= $bg_color; ?>" align="left" style="color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-bottom:2px;" height="30"><?= $tarif; ?></td>
                    <td bgcolor="<?= $bg_color; ?>" align="left" style="color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-bottom:2px;" height="30">
                        <?= $summ_month; ?>
                        <?php
                            if ($have_recalc && strstr($debtData_item['name_plat'], 'УТРИМАННЯ')) {

                                $summ = 0;
                                foreach ($recalcData as $item) {
                                    foreach ($item['list'] as $list_item) {
                                        $summ += $list_item['sum'];
                                    }
                                }

                                // в xml есть поле "Всього по о/рахунку(по періоду)", где дублирутеся общая сумма.
                                // То есть надо на два поделить то, что выше в цикле просуммировано
                                $summ /= 2;
                                ?>
                                <span style="font-size: 28px; font-style: italic; color: #00b86c; font-family: Times; cursor: help;" title="Перерахунок <?= $summ; ?> грн">&nbsp;i</span>
                                <?php
                            }
                        ?>
                    </td>
                    <td bgcolor="<?= $bg_color; ?>" align="left" style="color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-bottom:2px;" height="30"><?= $to_pay; ?></td>
                    <td bgcolor="<?= $bg_color; ?>" align="right" style="border-right:1px solid #eeeeee; color:#000000; <?= $_ff; ?> font-size:14px; padding:0px; line-height:18px; padding-bottom:2px; padding-right:30px;" height="30"><?= $oplat; ?></td>
                    <td style="padding:0px; line-height:10px;" width="18">&nbsp;</td>
                </tr>
                <?php
            }
        ?>
    </tbody></table>
    <?php
        // если последняя стока серая
        if ($number % 2 == 0) {
            $image = 'gray.png';
            $color = '#eeeeee';
            $border_bottom = '0px none;';
        } else {
            $image = 'white.png';
            $color = '#ffffff';
            $border_bottom = '1px solid #eeeeee;';
        }
    ?>
    <table <?= $_table_attr; ?>><tbody><tr>
        <td style="padding:0px; line-height:10px;" width="18" height="10">&nbsp;</td>
        <td width="10" style="padding:0px;" height="10">
            <table background="<?= $__img_path; ?>table-bottom-left-<?= $image; ?>" <?= $_table_attr; ?>><tbody><tr>
                <td style="padding:0px; line-height:10px;" height="10">&nbsp;</td>
            </tr></tbody></table>
        </td>
        <td bgcolor="<?= $color; ?>" style="background-color:<?= $color; ?>; padding:0px; line-height:10px; border-bottom:<?= $border_bottom; ?>" height="10">&nbsp;</td>
        <td width="10" style="padding:0px;" height="10">
            <table background="<?= $__img_path; ?>table-bottom-right-<?= $image; ?>" <?= $_table_attr; ?>><tbody><tr>
                <td style="padding:0px; line-height:10px;" height="10">&nbsp;</td>
            </tr></tbody></table>
        </td>
        <td style="padding:0px; line-height:10px;" width="18" height="10">&nbsp;</td>
    </tr></tbody></table>
    <?php
        $debt_sum = explode(',', $house['debt_sum']);
    ?>
    <table <?= $_table_attr; ?>><tbody><tr>
        <td align="left" width="370" style="line-height:18px; padding-left:69px; padding-top:11px;">
            <a href="<?= EXT_BASE_URL . rawurlencode('/cabinet/objects/' . $house['id'] . '/detailbill/?uid=' . $__userData['id'] . '&amp;hash2=' . $hash2); ?>" target="_blank"><img hspace="0" vspace="0" width="229" height="35" src="<?= $__img_path; ?>subsidy.png" alt="докладніше про нарахування"></a>
        </td>
        <td align="right" style="color:#282828; font-size:18px; <?= $_ff; ?> line-height:24px; padding-top:20px; padding-right:38px; padding-bottom:20px;">
            Разом до сплати:
            <span style="padding-left:10px; <?= $_text_color; ?> font-size:36px; font-weight:bold; <?= $_ff; ?> line-height:36px;"><?= $debt_sum[0]; ?></span><span style="<?= $_text_color; ?> font-size:18px; <?= $_ff; ?> line-height:24px;">,<?= $debt_sum[1]; ?> грн</span>
        </td>
    </tr></tbody></table>

    <table <?= $_table_attr; ?>><tbody><tr>
        <td align="left">&nbsp;</td>
        <td align="right" style="font-size:18px; <?= $_ff; ?> line-height:24px; padding-top:11px; padding-right:40px; padding-bottom:34px;">
            <a href="<?= htmlspecialchars($paybill_link); ?>" target="_blank">
                <img hspace="0" vspace="0" border="0" src="<?= $__img_path; ?>pay-btn.png" alt="Оплатить онлайн" width="261" height="60">
            </a>
        </td>
    </tr></tbody></table>
    <table <?= $_table_attr; ?>><tbody><tr>
        <td style="padding:0px; line-height:32px;" width="18">&nbsp;</td>
        <td align="left" style="font-size:12px; <?= $_ff; ?> line-height:24px; padding-top:11px; padding-right:40px; padding-bottom:34px;">
            <b class="hint-star">*</b> — з врахуванням пільг та перерахунків <br>
            <b class="hint-star">**</b> — довідково <br>
        </td>
    </tr></tbody></table>
    <table <?= $_table_attr; ?>><tbody><tr>
        <td width="111" bgcolor="#eeeeee" style="padding-bottom:27px; padding-top:27px; padding-left:18px; padding-right:11px;"><img src="<?= $__img_path; ?>paysystems.png" alt="ми приймаємо до сплати" height="25" width="111" hspace="0" vspace="0" border="0"></td>
        <td valign="middle" bgcolor="#eeeeee" style="color:#888888; font-style:italic; font-size:14px; <?= $_ff; ?> line-height:18px;">—&nbsp;&nbsp;ми приймаємо до сплати</td>
    </tr></tbody></table>
    <table <?= $_table_attr; ?>><tbody><tr><td style="padding-top:20px; padding-left:18px; padding-right:18px; padding-bottom:0px;" bgcolor="#01b671">
        <table <?= $_table_attr; ?>><tbody>
            <tr>
                <td valign="top" colspan="3" style="vertical-align:bottom; padding-bottom:30px; color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:15px; padding-left:11px;">
                    <span style="font-weight:bold; line-height:18px; color:#ffffff; <?= $_ff; ?> font-size:14px;">КК ЦКС <br></span>
                    2014—<?= date('Y'); ?> © <a style="text-decoration:none; color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:15px;" href="https://cks.com.ua/" target="_blank">https://cks.com.ua/</a>
                </td>
                <td valign="top" colspan="3" style="vertical-align:bottom; padding-bottom:30px; color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:15px; padding-left:11px;">
                    Лист містить дані для доступу до особистого кабінету, не передавайте його третім особам. <br>
                    Ви отримали цей лист оскільки бажаєте сплачувати за житлово-комунальні послуги одним платежем. Щоб відписатись, <a style="text-decoration:underline; color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:15px;" href="<?= EXT_BASE_URL . rawurlencode('/cabinet/settings/notifications/'); ?>" target="_blank">натисніть тут</a>
                </td>
                <td valign="top" width="20%" align="right" style="vertical-align:top;"><table <?= $_table_attr; ?>>
                    <tbody><tr>
                        <td>&nbsp;</td>
                        <td width="93" align="left" style="color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:18px;">
                            <a style="text-decoration:none; color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:18px;" href="tel:+380442474040">(044)&nbsp;247&nbsp;40&nbsp;40</a> <br>
                            <a style="text-decoration:none; color:#ffffff; <?= $_ff; ?> font-size:12px; line-height:18px;" href="mailto:<?= EMAIL_TO; ?>" target="_blank"><?= EMAIL_TO; ?></a>
                        </td>
                    </tr></tbody>
                </table></td>
            </tr>
        </tbody></table>
    </td></tr></tbody></table>
</td></tr></tbody></table>
<?php
    if (!$__is_email_mode) {
        require_once(ROOT . '/protected/scripts/yandex-metrika.php');
    }
?>
</body>
</html>