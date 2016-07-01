<?php

class UPC
{
    const UPC_FOLDER = '/protected/conf/payments/_test_upc';
    const ACTION = 'https://secure.upc.ua/go/enter';
    const TEST_ACTION = 'https://secure.upc.ua/ecgtest/enter';
    const CHECK_STATUS_URL = 'https://secure.upc.ua/go/service/01';
    const TEST_CHECK_STATUS_URL = 'https://ecg.test.upc.ua/ecgtest/service/01';

    public static function get_upc_error($code)
    {
        $arr = [
            'en' => [
                '000' => '',
                '101' => '',
                '105' => '',
                '108' => '',
                '111' => '',
                '116' => '',
                '130' => '',
                '290' => '',
                '291' => '',
                '401' => '',
                '402' => '',
                '403' => '',
                '404' => '',
                '405' => '',
                '406' => '',
                '407' => '',
                '408' => '',
                '409' => '',
                '410' => '',
                '411' => '',
                '412' => '',
                '413' => 'Unknown card type',
                '420' => '',
                '421' => '',
                '430' => '',
                '431' => '',
                '432' => 'Card is in stop list',
                '433' => 'The number of transactions has exceeded the limit',
                '434' => 'The merchant does not accept cards from the country',
                '435' => 'CLient IP address is on stop list',
                '436' => 'The sum of amount transactions has exceeded the limit',
                '437' => 'The limit of card number inputs has been exceeded',
                '438' => 'Unacceptable currency code',
                '439' => 'The time limit from request to authorization has been exceeded',
                '440' => 'The authorization time limit has been exceeded',
                '441' => 'MPI interaction problem',
                '450' => 'Recurrent payments are prohibited',
                '451' => 'MPI service not enabled',
                '452' => 'Card-to-Card Payment service not enabled',
                '501' => '',
                '502' => '',
                '503' => '',
                '504' => '',
                '505' => 'Invalid sequense of operations',
                '506' => 'Preauthorized transaction is expired',
                '507' => 'Preauthorized transaction already processed with payment',
                '508' => 'Invalid amount to pay a preauthorized transaction',
                '509' => 'Not able to trace back to original transaction',
                '510' => 'Refund is expired',
                '511' => 'Transaction was canceled by settlement action',
                '512' => 'Repeated reversal or refund',
                '601' => '',
            ],
            'ru' => [
                '000' => '',
                '101' => 'Неверный срок действия карты',
                '105' => 'Транзакция не разрешена банком-эмитентом',
                '108' => 'Карта утеряна или украдена',
                '111' => 'Несуществующая карта',
                '116' => 'Недостаточно средств',
                '130' => 'Превышен допустимый лимит расходов',
                '290' => 'Банк-издатель недоступен',
                '291' => 'Техническая или коммуникационная проблема',
                '401' => 'Ошибки формата',
                '402' => 'Ошибки в параметрах Acquirer/Merchant',
                '403' => 'Ошибки при соединении с ресурсом платежной системы (DS)',
                '404' => 'Ошибка аутентификации покупателя',
                '405' => 'Ошибка подписи',
                '406' => 'Превышена квота разрешенных транзакций',
                '407' => 'Торговец отключен от шлюза',
                '408' => 'Транзакция не найдена',
                '409' => 'Несколько транзакций найдено',
                '410' => 'Заказ уже был успешно оплачен',
                '411' => 'Некорректное время в запросе',
                '412' => 'Параметры заказа уже были получены ранее',
                '413' => '',
                '420' => 'Превышен дневной лимит транзакций',
                '421' => 'Превышена максимально разрешенная сумма транзакции',
                '430' => 'Транзакция запрещена на уровне платежного шлюза',
                '431' => 'Не разрешена транзакция без полной аутентификации по схеме 3-D Secure',
                '432' => '',
                '433' => '',
                '434' => '',
                '435' => '',
                '436' => '',
                '437' => '',
                '438' => '',
                '439' => '',
                '440' => '',
                '441' => '',
                '450' => '',
                '451' => '',
                '452' => '',
                '501' => 'Транзакция отменена пользователем',
                '502' => 'Сессия браузера устарела',
                '503' => 'Транзакция отменена магазином',
                '504' => 'Транзакция отменена шлюзом',
                '505' => '',
                '506' => '',
                '507' => '',
                '508' => '',
                '509' => '',
                '510' => '',
                '511' => '',
                '512' => '',
                '601' => 'Транзакция не завершена',
            ],
            'ua' => [
                '000' => '',
                '101' => 'Невірний термін дії картки',
                '105' => 'Транзакція не дозволена банком-емітентом',
                '108' => 'Картку втрачено чи вкрадено',
                '111' => 'Неіснуюча карта',
                '116' => 'Недостатньо коштів',
                '130' => 'Перевищено допустимий ліміт витрат',
                '290' => 'Банк-емітент недоступний',
                '291' => 'Технічна або комунікаційна проблема',
                '401' => 'Помилки формату',
                '402' => 'Помилки в параметрах Acquirer / Merchant',
                '403' => 'Помилки при з’єднанні з ресурсом платіжноїсистеми (DS)',
                '404' => 'Помилка аутентифікації покупця',
                '405' => 'Помилка підпису',
                '406' => 'Перевищена квота дозволених транзакцій',
                '407' => 'Торговець відключений від шлюзу',
                '408' => 'Транзакцію не знайдено',
                '409' => 'Кілька транзакцій знайдено',
                '410' => 'Замовлення вже було успішно сплачено',
                '411' => 'Некоректний час у запиті',
                '412' => 'Параметри замовлення вже були отримані раніше',
                '413' => 'Невідомий тип карти',
                '420' => 'Перевищено денний ліміт транзакцій',
                '421' => 'Перевищена максимально дозволена сума транзакції',
                '430' => 'Транзакція заборонена на рівні платіжного шлюзу',
                '431' => 'Не дозволена транзакція без повної аутентифікації по схемі 3-D Secure',
                '432' => 'Карта знаходиться у стоп-списку',
                '433' => 'Перевищено дозволений ліміт транзакцій',
                '434' => 'Мерчант не приймає карти з країни',
                '435' => 'IP-адреса клієнта заблоковано',
                '436' => 'Перевищено дозволений ліміт транзакцій',
                '437' => 'Перевищено ліміт використання кардрідера',
                '438' => 'Недопустимий код валюти',
                '439' => 'Термін від запиту до авторизації був перевищений',
                '440' => 'Термін авторизації був перевищений',
                '441' => 'Проблема взаємодії MPI',
                '450' => 'Зворотні платежі заборонені',
                '451' => 'Служба MPI не активна',
                '452' => 'Платіжний сервіс картка-картка не активний',
                '501' => 'Транзакція скасована користувачем',
                '502' => 'Сесія браузера застаріла',
                '503' => 'Транзакція скасована мерчантом',
                '504' => 'Транзакція скасована шлюзом',
                '505' => 'Невірна послідовність операцій',
                '506' => 'Попередні транзакції актуальні',
                '507' => 'Попередня транзакція  вже оброблена з оплатою',
                '508' => 'Неприпустима кількість платежів за попередньою транзакцією',
                '509' => 'Неможливо відстежити первісну транзакцію',
                '510' => 'Відкат актуальний',
                '511' => 'Розрахункову операцію було скасовано',
                '512' => 'Повторний запит або відкат',
                '601' => 'Транзакція не завершена',
            ],
        ];

        if (isset($arr['ua'][$code])) {
            return $arr['ua'][$code];
        }
        return 'Невідома помилка з кодом '.$code;
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
            case '8' : return 'Обов’язкові реквізити платежу не заповнені';
            case '9' : return 'Статус платежу не 20';
            case '10': return 'Помилка XML формату';
            
            case '100':
            default: return 'Невідома помилка з кодом '.$code;
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
