<?php
class CronTasks
{
    public static function findBrokenEmails()
    {
        $hostname = '{mail.gioc:110/pop3/novalidate-cert}INBOX';
        $username = 'no.reply@gioc.kiev.ua';
        $password = 'N3p12Q';

        /* try to connect */
        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect: ' . imap_last_error());
        $emails = imap_search($inbox, 'UNDELETED'); // grab emails

        if ($emails) {
            rsort($emails); // put the newest emails on top

            $pdo = PDO_DB::getPDO();
            $stm_upd = $pdo->prepare("UPDATE " . User::TABLE . " SET broken_email=1 WHERE email=? AND deleted=0 LIMIT 1");
            $stm_upd_subs = $pdo->prepare("UPDATE " . User::SUBSCRIBE_TABLE . " SET broken_email=1 WHERE email=? LIMIT 1");
            
            foreach ($emails as $email_number) {
                $overview = imap_fetch_overview($inbox, $email_number, 0);

                if (stristr($overview[0]->subject, '-- NOT SEND TO:')) {
                    $email = end(self::extractEmailAddress($overview[0]->subject));
                    $stm_upd->execute([$email]);
                    $stm_upd_subs->execute([$email]);
                    imap_delete($inbox, $email_number);
                }

                if (stristr($overview[0]->from, 'quarantine@i.ua')) {
                    // это письмо с просьбой перейти по ссылке, чтоб письмо попало во входящие
                    $message = imap_body($inbox, $email_number);
                    $links = self::extractLinks($message);
                    if (isset($links[0])) {
                        imap_delete($inbox, $email_number);
                        @file_get_contents($links[0]);
                    }
                }
            }
        }

        imap_expunge($inbox);
        imap_close($inbox); // close the connection
    }

    private static function extractEmailAddress($string)
    {
        foreach (preg_split('/\s/', $string) as $token) {
            $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
            if ($email !== false) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    private static function extractLinks($string)
    {
        $regex = "((https?|ftp)\:\/\/)?"; // SCHEME
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

        preg_match_all("/$regex/", $string, $matches); 

        return array_values(array_unique($matches[0]));
    }

    public static function sendFeedbackAnswer()
    {
        $list = PDO_DB::table_list(TABLE_PREFIX . 'feedback', 'answer_need_send=1');

        foreach ($list as $item) {
            
            if (!isset($email)) {
                $email = new Email();
                // $email->From = 'secretary@gioc-kmda.kiev.ua';
            }

            $email->clearAllRecipients();
            // $email->changeMXToQuick();

            $email->send(
                [$item['email'], "{$item['name']} {$item['fathername']}"],
                $item['subject'],
                '',
                'feedback_answer',
                [
                    'answer' => $item['answer'],
                    'request' => nl2br(htmlspecialchars($item['text']))
                ]
            );
            PDO_DB::update(['answer_need_send' => 0], TABLE_PREFIX . 'feedback', $item['id']);
        }
    }

    public static function sendReportAboutTasLink($time_from, $time_to)
    {
        $pdo = PDO_DB::getPDO();
        $to_emails = ['saifudinova@gerc.ua', 'cri2net@gmail.com', 'di.yarovoy@gmail.com'];

        $payment_systems = [
            'visa'          => 'Аваль Visa',
            'mastercard'    => 'Аваль Mastercard',
            'tas'           => 'ТасКомБанк',
            'khreshchatyk'  => 'Картка Киянина (Крещатик)',
            'oschad'        => 'Картка Киянина (Ощадбанк)',
            'oschad_mycard' => 'Моя Картка (Ощадбанк)',
            '_test_upc'     => 'UPC/тестовий',
        ];

        $objPHPExcel = new PHPExcel(); // Create new PHPExcel object

        // Set properties
        $objPHPExcel->getProperties()->setTitle("GIOC. Платежи через ТасЛинк за " . date('Y-m-d', $time_from));
        $objPHPExcel->setActiveSheetIndex(0);

        $abc = "ABCDEFGHIJLKLMNOP";
        for ($i = 0; $i < strlen($abc); $i++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($abc[$i])->setAutoSize(true);
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A1', "ID");
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "ID пользователя");
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "Пользователь");
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "Время начала оплаты");
        $objPHPExcel->getActiveSheet()->setCellValue('E1', "Тип");
        $objPHPExcel->getActiveSheet()->setCellValue('F1', "Шлюз");
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "PAN");
        $objPHPExcel->getActiveSheet()->setCellValue('H1', "ID PLAT KLIENT (в оракле)");
        $objPHPExcel->getActiveSheet()->setCellValue('I1', "К оплате");
        $objPHPExcel->getActiveSheet()->setCellValue('J1', "Комиссия");
        $objPHPExcel->getActiveSheet()->setCellValue('K1', "Общая сумма");
        $objPHPExcel->getActiveSheet()->setCellValue('L1', "Количество услуг в платеже");
        $objPHPExcel->getActiveSheet()->setCellValue('M1', "Код подтверждения");
        $objPHPExcel->getActiveSheet()->setCellValue('N1', "Время от процессинга");
        $objPHPExcel->getActiveSheet()->setCellValue('O1', "ID у процессинга");
        $objPHPExcel->getActiveSheet()->setCellValue('P1', "Платёжная система");

        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'inside' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('argb' => 'FF000000'),
                ),
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THICK,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
        );
        $objPHPExcel->getActiveSheet()->getStyle('A1:P1')->applyFromArray($styleThinBlackBorderOutline);

        $stm = $pdo->prepare("SELECT * FROM " . ShoppingCart::TABLE . " WHERE processing='tas' AND status='success' AND go_to_payment_time>? AND go_to_payment_time<?");
        $stm->execute([$time_from, $time_to]);

        $counter = 2;

        while ($row = $stm->fetch()) {

            $user = PDO_DB::row_by_id(User::TABLE, $row['user_id']);

            $row['processing_data'] = (array)(json_decode($row['processing_data']));
            $row['processing_data']['dates'] = (array)$row['processing_data']['dates'];
            $row['processing_data']['requests'] = (array)$row['processing_data']['requests'];
            $actual_date = $row['processing_data']['dates'][count($row['processing_data']['dates']) - 1];
            $actual_upc_data = (array)$row['processing_data']['requests'][$actual_date];

            switch ($row['type']) {
                case 'komdebt':
                    $type = 'Коммунальные услуги';
                    break;

                case 'gai':
                    $type = 'Штрафы за нарушения ПДД';
                    break;
                
                case 'kinders':
                    $type = 'Садики (питание)';
                    break;
                
                default:
                    $type = $row['type'];
            }

            if (empty($actual_upc_data['PAN'])) {
                $row['paysystem'] = '';
            } elseif ($actual_upc_data['PAN'][0] == '4') {
                $row['paysystem'] = 'VISA';
            } elseif ($actual_upc_data['PAN'][0] == '5') {
                $row['paysystem'] = 'MasterCard';
            } else {
                $row['paysystem'] = 'Другое';
            }

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$counter, $row['id']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$counter, $row['user_id']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$counter, "{$user['lastname']} {$user['name']} {$user['fathername']}");
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$counter, date('Y-m-d H:i:s', $row['go_to_payment_time']));
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$counter, $type);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$counter, $payment_systems[$row['processing']]);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$counter, $actual_upc_data['PAN']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$counter, $row['reports_id_plat_klient']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$counter, $row['summ_plat']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$counter, $row['summ_komis']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$counter, $row['summ_total']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$counter, $row['count_services']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$counter, $actual_upc_data['APPROVAL']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$counter, $actual_upc_data['TIME']);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$counter, $row['processing_data']['first']->oid);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$counter, $row['paysystem']);

            $counter++;
        }

        // Rename sheet
        $objPHPExcel->getActiveSheet()->setTitle('GIOC');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        
        ob_start();
        $objWriter->save('php://output');
        $xls = ob_get_contents();
        ob_end_clean();

        $email = new Email();
        $email->addStringAttachment($xls, "GIOC-Taslink-report " . date('Y-m-d_', $time_from) . date('Y-m-d', $time_to) . '.xls');
        $email->Subject = "GIOC. Платежи через ТасЛинк за " . date('Y-m-d_', $time_from) . date('Y-m-d', $time_to);
        
        foreach ($to_emails as $to_email) {
            $email->clearAllRecipients();
            $email->AddAddress($to_email);
            $email->call_phpmailer_send();
        }
    }
}
