<?php

class UPC
{
    const UPC_FOLDER = '/protected/conf/payments/_test_upc';
    const ACTION = 'https://secure.upc.ua/go/enter';
    const TEST_ACTION = 'https://secure.upc.ua/ecgtest/enter';

    public static function get_upc_error($code)
    {
        switch ($code) {
            case '000': return ''; // all ok
    
            case '101': return 'Неверный срок действия карты';
            case '105': return 'Транзакция не разрешена банком-эмитентом';
            case '108': return 'Карта утеряна или украдена';
            case '111': return 'Несуществующая карта';
            case '116': return 'Недостаточно средств';
            case '130': return 'Превышен допустимый лимит расходов';
            case '290': return 'Банк-издатель недоступен';
            case '291': return 'Техническая или коммуникационная проблема';
            case '401': return 'Ошибки формата';
            case '402': return 'Ошибки в параметрах Acquirer/Merchant';
            case '403': return 'Ошибки при соединении с ресурсом платежной системы (DS)';
            case '404': return 'Ошибка аутентификации покупателя';
            case '405': return 'Ошибка подписи';
            case '406': return 'Превышена квота разрешенных транзакций';
            case '407': return 'Торговец отключен от шлюза';
            case '408': return 'Транзакция не найдена';
            case '409': return 'Несколько транзакций найдено';
            case '410': return 'Заказ уже был успешно оплачен';
            case '411': return 'Некорректное время в запросе';
            case '412': return 'Параметры заказа уже были получены ранее';
            case '413': return 'Unknown card type';
            case '420': return 'Превышен дневной лимит транзакций';
            case '421': return 'Превышена максимально разрешенная сумма транзакции';
            case '430': return 'Транзакция запрещена на уровне платежного шлюза';
            case '431': return 'Не разрешена транзакция без полной аутентификации по схеме 3-D Secure';
            case '432': return 'Card is in stop list';
            case '433': return 'The number of transactions has exceeded the limit';
            case '434': return 'The merchant does not accept cards from the country';
            case '435': return 'CLient IP address is on stop list';
            case '436': return 'The sum of amount transactions has exceeded the limit';
            case '437': return 'The limit of card number inputs has been exceeded';
            case '438': return 'Unacceptable currency code';
            case '439': return 'The time limit from request to authorization has been exceeded';
            case '440': return 'The authorization time limit has been exceeded';
            case '441': return 'MPI interaction problem';
            case '450': return 'Recurrent payments are prohibited';
            case '451': return 'MPI service not enabled';
            case '452': return 'Card-to-Card Payment service not enabled';
            case '501': return 'Транзакция отменена пользователем';
            case '502': return 'Сессия браузера устарела';
            case '503': return 'Транзакция отменена магазином';
            case '504': return 'Транзакция отменена шлюзом';
            case '505': return 'Invalid sequense of operations';
            case '506': return 'Preauthorized transaction is expired';
            case '507': return 'Preauthorized transaction already processed with payment';
            case '508': return 'Invalid amount to pay a preauthorized transaction';
            case '509': return 'Not able to trace back to original transaction';
            case '510': return 'Refund is expired';
            case '511': return 'Transaction was canceled by settlement action';
            case '512': return 'Repeated reversal or refund';
            case '601': return 'Транзакция не завершена';

            default: return 'Произошла неизвестная ошибка '.$code;
        }
    }

    public static function get_error($code)
    {
        switch ($code) {
            case '':
            case '0':
                return '';
            
            case '1' : return 'Касовий день закритий';
            case '2' : return 'Стан фіксації (блокування) платежу. Вносити зміни не можна.';
            case '4' : return 'Немає платежу';
            case '5' : return 'Немає реквізиту';
            case '6' : return 'Сума платежа дорівнює 0';
            case '7' : return 'Платіж з таким id_plat_klient вже був проведений';
            case '8' : return 'Обов\'язкові реквізити платежу не заповнені';
            case '9' : return 'Статус платежу не 20';
            case '10': return 'Помилка XML формату';
            
            case '100':
            default: return 'Произошла неизвестная ошибка '.$code;
        }
    }

    public static function check_signature($data, $pubkeypath = null)
    {
        $string = 'MerchantId;TerminalId;PurchaseTime;OrderId,Delay;Xid;CurrencyId,AltCurrencyId;Amount,AltAmount;SessionData;TranCode;ApprovalCode;';
        $data['Signature'] = base64_decode($data['Signature']);

        $string = str_replace('MerchantId', $data['MerchantID'], $string);
        $string = str_replace('TerminalId', $data['TerminalID'], $string);
        $string = str_replace('PurchaseTime', $data['PurchaseTime'], $string);
        $string = str_replace('OrderId,Delay', $data['OrderID'], $string);
        $string = str_replace('Xid', $data['XID'], $string);
        $string = str_replace('CurrencyId,AltCurrencyId', $data['Currency'], $string);
        $string = str_replace('Amount,AltAmount', $data['TotalAmount'], $string);
        $string = str_replace('SessionData', $data['SD'], $string);
        $string = str_replace('TranCode', $data['TranCode'], $string);
        $string = str_replace('ApprovalCode', $data['ApprovalCode'], $string);

        $pubkeyid = ($pubkeypath == null)
            ? openssl_get_publickey(file_get_contents(ROOT . self::UPC_FOLDER . '/work-server.crt'))
            : openssl_get_publickey(file_get_contents($pubkeypath));

        return (openssl_verify($string, $data['Signature'], $pubkeyid) == 1);
    }
}
