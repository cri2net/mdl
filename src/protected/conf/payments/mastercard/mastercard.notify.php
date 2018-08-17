<?php

    use cri2net\php_pdo_db\PDO_DB;
    
    header("Content-Type: text/plain; charset=UTF-8");

    $_sert = ROOT . "/protected/conf/payments/$paysystem/work-server.crt";
    $good_signature = UPC::check_signature($_POST, $_sert);
    $date = date('d-m-Y H:i:s');
   
    $mess = $date."\r\n";
    $mess .= "IP: ". USER_REAL_IP ."\r\n";
    $mess .= ($good_signature) ? "Signature ok\r\n" : "Signature bad\r\n";
    $mess .= "POST: ".var_export($_POST, true)."\r\n\r\n\r\n\r\n";
   
    $log_folder = ROOT . "/protected/logs/paysystems/$paysystem";
    if (!file_exists($log_folder)) {
        mkdir($log_folder, 0755, true);
    }

    $handle = fopen($log_folder . "/$paysystem.txt", 'a+');
    fwrite($handle, $mess);
    fclose($handle);

    if (!$good_signature) {
        throw new Exception("invalid Signature");
        exit();
    }

    $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $_POST['OrderID']);

    if ($_payment === null) {
        throw new Exception("Unknow OrderID {$_POST['OrderID']}");
        exit();
    }

    $processing_data = (array)(@json_decode($_payment['processing_data']));
    $processing_data['requests'] = (array)$processing_data['requests'];
    $processing_data['dates'][] = $date;
    $processing_data['requests'][$date] = $_POST;


    $to_update = [
        'processing_data' => json_encode($processing_data),
        'send_payment_status_to_reports' => 0
    ];

    switch ($_POST['TranCode']) {
        case '000': // all ok
        case '410': // Заказ уже был успешно оплачен
            $to_update['status'] = 'success';
            break;
        
        case '601': // Транзакция не завершена
            // do nothing
            break;
        
        case '105': // Транзакция не разрешена банком-эмитентом
        case '116': // Недостаточно средств
        case '111': // Несуществующая карта
        case '108': // Карта утеряна или украдена
        case '101': // Неверный срок действия карты
        case '130': // Превышен допустимый лимит расходов
        case '290': // Банк-издатель недоступен
        case '291': // Техническая или коммуникационная проблема
        case '401': // Ошибки формата
        case '402': // Ошибки в параметрах Acquirer/Merchant
        case '403': // Ошибки при соединении с ресурсом платежной системы (DS)
        case '404': // Ошибка аутентификации покупателя
        case '405': // Ошибка подписи
        case '406': // Превышена квота разрешенных транзакций
        case '407': // Торговец отключен от шлюза
        case '408': // Транзакция не найдена
        case '409': // Несколько транзакций найдено
        case '411': // Некорректное время в запросе
        case '412': // Параметры заказа уже были получены ранее
        case '420': // Превышен дневной лимит транзакций
        case '421': // Превышена максимально разрешенная сумма транзакции
        case '430': // Транзакция запрещена на уровне платежного шлюза
        case '431': // Не разрешена транзакция без полной аутентификации по схеме 3-D Secure
        case '501': // Транзакция отменена пользователем
        case '502': // Сессия браузера устарела
        case '503': // Транзакция отменена магазином
        case '504': // Транзакция отменена шлюзом
       
        default:
            $to_update['status'] = 'error';
    }

    PDO_DB::update($to_update, ShoppingCart::TABLE, $_payment['id']);
    ShoppingCart::send_payment_status_to_reports($_payment['id']);
?>
MerchantID="<?= $_POST['MerchantID']; ?>"
TerminalID="<?= $_POST['TerminalID']; ?>"
OrderID="<?= $_payment['id']; ?>"
Currency="<?= $_POST['Currency']; ?>"
TotalAmount="<?= $_POST['TotalAmount']; ?>"
XID="<?= $_POST['XID']; ?>"
PurchaseTime="<?= $_POST['PurchaseTime']; ?>"
Response.action="approve"
Response.reason=""
Response.forwardUrl="<?= BASE_URL . '/redirect-to-journal/?id='. $processing_data['openid']->id; ?>"