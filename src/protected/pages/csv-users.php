<?php
    $objPHPExcel = PHPExcel_IOFactory::load(PROTECTED_DIR . '/templates/cks-for-mailing.xlsx');
    $objPHPExcel->setActiveSheetIndex(0);

    $users = User::getUsersList(5);
    $debt = new KomDebt();
    $row = 4;

    $obj_letters = 'EFGHIJKLMNOP';
    foreach($users as $user) {
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue("A$row", $user['id'])
                    ->setCellValue("B$row", trim($user['lastname'] . ' ' . $user['name'] . ' ' . $user['fathername']))
                    ->setCellValue("C$row", $user['email'])
                    ->setCellValue("D$row", $user['mob_phone']);
        
        //echo "<b>{$user['email']}</b><br/>";
        $flats = Flat::getUserFlats($user['id']);
        $sum = 0; $sumo = 0; $c = 0; $cf = 0;
        foreach($flats as $flat) {
            if($c++ == 3) break;

            $debtData = $debt->getData($flat['flat_id'], null, 0);
            $flat['debt_sum'] = $debtData['full_dept'];

            $oplat_timestamp = strtotime('first day of next month', $debtData['timestamp']);
            $oplat_timestamp = strtotime('first day of next month', $oplat_timestamp);
            try {
                $oplat = (int)$debt->getPayOnThisMonth($flat['flat_id'], date('1.m.Y', $oplat_timestamp));
            } catch (Exception $e) {
                $oplat = 0;
            }

            $sum += $flat['debt_sum'];
            $sumo += $oplat;
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue("{$obj_letters[$cf++]}$row", $flat['address'])
                        ->setCellValue("{$obj_letters[$cf++]}$row", $oplat > 0 ? 'да' : 'нет')
                        ->setCellValue("{$obj_letters[$cf++]}$row", $flat['debt_sum']);
        }
        $row++;
    }

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save(PROTECTED_DIR . '/templates/cks-for-mailing-' . microtime(true) . '.xlsx');
    echo "Done";