<?php

use cri2net\php_pdo_db\PDO_DB;

class CronTasks
{
    public static function sendReportAboutTasLink($time_from, $time_to)
    {
        $pdo = PDO_DB::getPDO();
        $to_emails = ['saifudinova@gerc.ua', 'cri2net@gmail.com'];

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
        $objPHPExcel->getProperties()->setTitle("CKS. Платежи через ТасЛинк за " . date('Y-m-d', $time_from));
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
        $objPHPExcel->getActiveSheet()->setTitle('CKS');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        
        ob_start();
        $objWriter->save('php://output');
        $xls = ob_get_contents();
        ob_end_clean();

        $email = new Email();
        $email->addStringAttachment($xls, "CKS-Taslink-report " . date('Y-m-d_', $time_from) . date('Y-m-d', $time_to) . '.xls');
        $email->Subject = "CKS. Платежи через ТасЛинк за " . date('Y-m-d_', $time_from) . date('Y-m-d', $time_to);
        
        foreach ($to_emails as $to_email) {
            $email->clearAllRecipients();
            $email->AddAddress($to_email);
            $email->call_phpmailer_send();
        }
    }
}
