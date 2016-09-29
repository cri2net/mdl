<?php

use cri2net\php_pdo_db\PDO_DB;

class TasLink
{
    const PASSWORD_INIT_SESSION = 'TASLINK_ORDER_REQ';
    const PASSWORD_MAKE_PAYMEENT = 'TASLINK_FIN_REQ';
    const PASSWORD_FIN_RESPONSE = 'TASLINK_FIN_RESP';
    const PASSWORD_STATUS_REQ = 'TASLINK_STATUS_REQ';
    const PASSWORD_STATUS_RESP = 'TASLINK_STATUS_RESP';
    const PASSWORD_BATCH_REQ = 'TASLINK_BATCH_REQ';
    const IFRAME_SRC = 'https://gerc-payments.taslink.com.ua/gioc.html?oid=';

    public $session_id;
    public $payment_id;
    public $payment_type;
    
    public function __construct($type = 'komdebt')
    {
        $this->payment_type = $type;
    }

    public static function get_API_URL($key)
    {
        $urls = [];
        $urls['INIT_SESSION']  = 'https://gerc-payments.taslink.com.ua/getOrder.php';
        $urls['MAKE_PAYMEENT'] = 'https://gerc-payments.taslink.com.ua/finAction.php';
        $urls['CHECK_STATUS']  = 'https://gerc-payments.taslink.com.ua/getStatus.php';
        $urls['GET_BATCH']     = 'https://gerc-payments.taslink.com.ua/getBatch.php';

        return $urls[$key];
    }

    public function checkNotifySignature($json)
    {
        $real_sign = md5(strtoupper(strrev($json->ORDERID) . strrev($json->TRANID) . strrev($json->AMOUNT) . strrev(self::PASSWORD_FIN_RESPONSE)));
        return (strcasecmp($real_sign, $json->SIGN) == 0);
    }

    public function getTermname()
    {
        // тестовый терминал
        // return '000000020000001';
        
        switch ($this->payment_type) {
            case 'komdebt': return 'TE0020';
            case 'budget':  return 'TE0021';
            case 'other':   return 'TE0022';
        }
    }

    /**
     * Инициализация сессии для дальнейшего проброса человека на фрейм
     * @param  integer | string $payment_id ID платежа в системе ГЕРЦ
     * @param  string           $type       тип транзакции. OPTIONAL
     * @return string                       id сессии
     */
    public function initSession($payment_id, $type = 'Purchase')
    {
        $this->payment_id = $payment_id;
        $termname = $this->getTermname();
        $str_to_sign = strrev($payment_id) . strrev($termname) . strrev($type);
        $sign = md5(strtoupper($str_to_sign . strrev(self::PASSWORD_INIT_SESSION)));

        $data = [
            'tranid'   => $payment_id, // уникальный идентификатор в системе Герц
            'termname' => $termname,   // имя терминала в платежном шлюзе Тас Линк. Оно же тег (для определения типа платежа)
            'type'     => $type,       // тип операции
            'sign'     => $sign,       // signature
        ];

        $url = self::get_API_URL('INIT_SESSION');
        $session_id = self::httpPost($url, json_encode($data));

        $processing_data = [
            'first' => [
                'oid'      => $session_id,
                'termname' => $termname,
                'type'     => $type
            ]
        ];
        $arr = [
            'processing_data' => json_encode($processing_data)
        ];

        PDO_DB::update($arr, ShoppingCart::TABLE, $payment_id);
        $this->session_id = $session_id;
        return $session_id;
    }

    public function checkStatus($payment_id)
    {
        $payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $payment_id);
        $payment['processing_data'] = json_decode($payment['processing_data']);
        if (!isset($payment['processing_data']->cron_check_status)) {
            $payment['processing_data']->cron_check_status = [];
        }

        $type = 'GetStatus';
        $oid = $payment['processing_data']->first->oid;
        $termname = $payment['processing_data']->first->termname;
        $sign = md5(strtoupper(strrev($type) . strrev($termname) . strrev($oid) . strrev(self::PASSWORD_STATUS_REQ)));

        $send_data = [
            'type'     => $type,     // тип операции
            'termname' => $termname, // имя терминала в платежном шлюзе Тас Линк
            'oid'      => $oid,      // ордер, в рамках которого было иниццировано платеж  
            'sign'     => $sign,     // signature
        ];

        $url = self::get_API_URL('CHECK_STATUS');
        $response = self::httpPost($url, json_encode($send_data));
        $response = @json_decode($response);
        $data = [];

        try {
            if ($response === null) {
                throw new Exception("Invalid JSON");
            }

            $real_sign = md5(strtoupper(strrev($response->oid) . strrev($response->respcode) . strrev($response->tranid) . strrev(self::PASSWORD_STATUS_RESP)));
            if (strcasecmp($real_sign, $response->sign) !== 0) {
                throw new Exception("Error Signature");
            }

            if ($response->respcode.'' == '') {
                throw new Exception("Транзакция не завершена");
            }

            $success = in_array($response->respcode, array('000', '001'));
            if ($success) {
                $data['status'] = 'success';
            } else {
                $data['status'] = 'error';
            }
            
            $payment['processing_data']->cron_check_status[] = [
                'timestamp' => microtime(true),
                'raw_data' => $response,
                'request' => $send_data
            ];
        } catch (Exception $e) {
            $discard = (microtime(true) - $payment['paytime'] > 1800);

            if ($discard) {
                $data['status'] = 'timeout';
                $data['send_payment_status_to_reports'] = 1;

                $payment['processing_data']->cron_check_status[] = [
                    'timestamp' => microtime(true),
                    'raw_data' => $response,
                    'request' => $send_data
                ];
            }
        }

        if (!empty($data)) {
            $data['processing_data'] = json_encode($payment['processing_data']);
            PDO_DB::update($data, ShoppingCart::TABLE, $payment_id);
            ShoppingCart::send_payment_status_to_reports($payment_id);
        }
    }

    public function makePayment($amount, $commission)
    {
        $total = $amount + $commission;
        $str_to_sign = strrev($this->payment_id) . strrev($this->session_id) . strrev($total);
        $sign = md5(strtoupper($str_to_sign . strrev(self::PASSWORD_MAKE_PAYMEENT)));

        $data = [
            'tranid' => $this->payment_id, // уникальный идентификатор в системе Герц
            'oid'    => $this->session_id, // уникальный идентификатор сессии (из 3-го шага)
            'amount' => $amount,           // сумма платежа без комиссии
            'fee'    => $commission,       // комиссия
            'total'  => $total,            // общая сумма
            'sign'   => $sign,             // signature
        ];

        $url = self::get_API_URL('MAKE_PAYMEENT');
        $response = self::httpPost($url, json_encode($data));
        if ($response == 'Request accepted') {
            return true;
        }

        throw new Exception($response);
        return false;
    }

    public function getBatch($date = null)
    {
        if ($date == null) {
            $date = date('dmY');
        }
        $type = 'getBatch';
        $sign = md5(strtoupper(strrev($type) . strrev($date) . strrev(self::PASSWORD_BATCH_REQ)));
        $url = self::get_API_URL('GET_BATCH');

        $data = [
            'type'    => $type,
            'batchno' => $date, // день в формате DDMMYYYY, напр. 01012016
            'sign'    => $sign, // signature
        ];

        $response = self::httpPost($url, json_encode($data));

        return $response;
    }

    /**
     * Отправка запроса на сервер ТАС.
     * Реализация отличается от метода в классе Http, целесообразно частично продублировать код в этом классе
     * @param  string  $url   URL to POST
     * @param  string  $data  final data
     * @return string         raw answer
     */
    public static function httpPost($url, $data)
    {
        $ch = curl_init();

        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_HEADER         => false,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => array(
                'Content-Length: ' . strlen($data)
            )
        );

        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function getErrorDescription($trancode)
    {
        $arr = [
            'ru' => [
                '000' => '',
                '001' => '',
                '003' => 'Транзакция выполнена, требуется дополнительная идентификация',
                '007' => 'Административная транзакция выполнена успешно',
                '050' => 'Финансовая транзакция не выполнена',
                '020' => 'Код ошибки - 20. Неизвестная системная ошибка.',
                '051' => 'Карта клиента просрочена',
                '052' => 'Превышено число попыток ввода PIN',
                '053' => 'Не удалось маршрутизировать транзакцию',
                '055' => 'Транзакция имеет некорректные атрибуты или данная операция не разрешена',
                '056' => 'Запрашиваемая операция не поддерживается хостом',
                '057' => 'Карта клиента имеет статус «потеряна» или «украдена»',
                '058' => 'Карта клиента имеет неправильный статус',
                '059' => 'Карта клиента имеет ограниченные возможности',
                '060' => 'Не найден вендор с указанным номером счета',
                '061' => 'Неверное количество информационных полей для заданного вендора',
                '062' => 'Неверный формат информационного поля платежа',
                '063' => 'Не найден prepaid-код',
                '064' => 'Track2 карты клиента содержит неверную информацию',
                '069' => 'Неверный формат сообщения',
                '074' => 'Невозможно авторизовать',
                '075' => 'Неверный PAN карты',
                '076' => 'На счете не хватает средств',
                '078' => 'Произошло дублирование транзакции',
                '082' => 'Превышение количества использований карты клиента',
                '085' => 'Невозможно выдать баланс',
                '095' => 'Превышение лимита по сумме',
                '100' => 'Невозможно провести транзакцию',
                '101' => 'Невозможно авторизовать – необходимо позвонить издателю карты ',
                '105' => 'Данный тип карт не поддерживается',
                '200' => 'Неправильный счет клиента',
                '201' => 'Неправильный PIN',
                '205' => 'Некорректная сумма',
                '209' => 'Неверный код транзакции',
                '210' => 'Неверное значение CAVV',
                '211' => 'Неверное значение CVV2 ',
                '212' => 'Не найдена оригинальная транзакция для слипа',
                '213' => 'Слип принимается повторно',
                '800' => 'Ошибка формата',
                '801' => 'Не найдена оригинальная транзакция для реверса',
                '809' => 'Неверная операция закрытия периода',
                '810' => 'Произошел тайм-аут',
                '811' => 'Системная ошибка',
                '820' => 'Неправильный идентификатор терминала',
                '880' => 'Был послан последний пакет - прогрузка успешно завершена',
                '881' => 'Предыдущий этап прогрузки был успешно выполнен –имеются еще данные',
                '882' => 'Прогрузка терминала остановлена. Необходимо позвонить в процессинговый центр',
                '897' => 'Получена неверная криптограмма в транзакции',
                '898' => 'Получен неверный MAC',
                '899' => 'Ошибка синхронизации',
                '900' => 'Превышено число попыток ввода PIN. Требуется захват карты.',
                '901' => 'Карта просрочена, требуется захват карты.',
                '909' => 'Требуется захват карты',
                '959' => 'Административная транзакция не поддерживается',
                '-1'  => 'Системная ошибка. POS не отвечает',
                '500' => '',
                '501' => 'Транзакция выполнена успешно',
                '502' => 'ТТранзакция выполнена успешно на частичную сумму',
                '503' => 'Транзакция выполнена успешно только на сумму покупки (для транзакции 118 – Purchase with Cashback)',
                '510' => 'Нет номера счета в запросе транзакции, есть несколько счетов указанного типа, и терминал поддерживает выбор счета',
                '511' => 'Необходимо сменить PVV (разрешена только транзакция PIN Change)',
                '512' => 'Необходимо подтвердить результаты проверки платежа в системе online-приема платежей',
                '513' => 'Транзакция запроса списка уведомлений выполнена успешно.',
                '514' => 'Необходимо подтвердить результаты предпроверки операции',
                '515' => 'Не найдена оригинальная транзакция',
                '516' => 'Слип уже принят',
                '517' => 'Ошибка в реквизитах платежа',
                '520' => 'Невозможно захватить Prepaid-код',
                '521' => 'Баланс корр. счета исчерпан',
                '522' => 'Превышен эквайринговый лимит',
                '524' => 'Истек срок действия диамического PVV',
                '525' => 'Слабый PIN',
                '540' => 'Карта потеряна',
                '541' => 'Карта украдена',
                '549' => 'Недопустимый тип платежа для данного вендора',
                '550' => 'Несанкционированное использование',
                '551' => 'Истек срок действия карты',
                '552' => 'Неверная карта',
                '553' => 'Неверный PIN-код',
                '554' => 'Системная ошибка',
                '555' => 'Неподходящая транзакция',
                '556' => 'Неподходящий счет',
                '557' => 'Транзакция не поддерживается',
                '558' => 'Карта ограничена (данная операция по карте не разрешена)',
                '559' => 'Недостаточно средств на счете',
                '560' => 'Превышен лимит на число использований карты',
                '561' => 'Лимит на снятие наличных будет превышен',
                '562' => 'Достигнут или превышен лимит на число неверных вводов PIN-кода',
                '563' => 'Достигнут лимит на снятие наличных',
                '564' => 'Достигнут лимит на депозит',
                '565' => 'Нет информации для предоставления отчета по счету',
                '566' => 'Предоставление отчета по счету невозможно (запрещено)',
                '567' => 'Недопустимая сумма',
                '568' => 'Транзакция отвергнута внешним хостом',
                '569' => 'Несогласованный запрос (данная карта не обслуживается в данном терминале)',
                '571' => 'Необходимо обратиться к издателю',
                '572' => 'Авторизатор недоступен',
                '573' => 'Ошибка маршрутизации',
                '574' => 'Ошибка формата',
                '575' => 'Транзакция отвергнута внешним хостом по специальному условию (владелец карты подозревается в мошенничестве)',
                '580' => 'Неверный CVV',
                '581' => 'Неверный CVV2',
                '582' => 'Неверная транзакция (транзакция не разрешена с такими условиями проведения)',
                '583' => 'Лимит на число неверных вводов PIN-кода  УЖЕ достигнут  (т.е. ранее был достигнут лимит на число неверных вводов PIN-кода, после чего был введен верный PIN)',
                '584' => 'Неверное значение проверочного числа 3D Secure Cardholder Authentication Verification Value',
                '585' => 'Неверное значение криптограммы ARQC',
            ],
            'en' => [
                '000' => '',
                '001' => '',
                '003' => 'Approved, additional identification requested',
                '007' => 'Approved administrative transaction',
                '050' => 'General',
                '020' => 'Code - 20. Unknown system Error. ',
                '051' => 'Expired card',
                '052' => 'Number of PIN tries exceeded',
                '053' => 'No sharing allowed',
                '055' => 'Invalid transaction',
                '056' => 'Transaction not supported by institution',
                '057' => 'Lost or stolen card',
                '058' => 'Invalid card status',
                '059' => 'Restricted status',
                '060' => 'Account not found',
                '061' => 'Wrong customer information for payment',
                '062' => 'Customer information format error',
                '063' => 'Prepaid Code not found',
                '064' => 'Bad track information Track2',
                '069' => 'Bad message edit',
                '074' => 'Unable to authorize',
                '075' => 'Invalid credit PAN',
                '076' => 'Insufficient funds',
                '078' => 'Duplicate transaction received',
                '082' => 'Maximum number of times used',
                '085' => 'Balance not allowed',
                '095' => 'Amount over maximum',
                '100' => 'Unable to process',
                '101' => 'Unable to authorize – call issuer',
                '105' => 'Card not supported',
                '200' => 'Invalid account',
                '201' => 'Incorrect PIN',
                '205' => 'Invalid advance amount',
                '209' => 'Invalid transaction code',
                '210' => 'Bad CAVV',
                '211' => 'Bad Cvv2',
                '212' => 'Original transaction not found for slip',
                '213' => 'Slip already received',
                '800' => 'Format error',
                '801' => 'Original transaction not found',
                '809' => 'Invalid close transaction',
                '810' => 'Transaction timeout',
                '811' => 'System error',
                '820' => 'Invalid terminal identifier',
                '880' => 'Download has been received in its entirety',
                '881' => 'Download received successfully and there is more data for this download',
                '882' => 'Download aborted (call for service)',
                '897' => 'Invalid cryptogram',
                '898' => 'Invalid MAC',
                '899' => 'Sequence error—resync',
                '900' => 'Pin Tries Limit Exceeded',
                '901' => 'Expired Card',
                '909' => 'External Decline Special Condition',
                '959' => 'Administrative transaction not supported',
                '-1'  => 'System Error. Pos Not Answer',
                '501' => 'Approved',
                '502' => 'Partially approved',
                '503' => 'Purchase only approved',
                '510' => 'Should select account number',
                '511' => 'Should change PVV',
                '512' => 'Confirm Payment Precheck',
                '513' => 'Select Bill',
                '514' => 'Customer confirmation requested',
                '515' => 'Original transaction not found',
                '516' => 'Slip already received',
                '517' => 'Personal information input error',
                '520' => 'Prepaid code not found',
                '521' => 'Corresponding account exhausted',
                '522' => 'Acquirer limit exceeded',
                '524' => 'Dynamic PVV Expired',
                '525' => 'Weak PIN',
                '540' => 'Lost card',
                '541' => 'Stolen card',
                '549' => 'Ineligible vendor account',
                '550' => 'Unauthorized usage',
                '551' => 'Expired card',
                '552' => 'Invalid card',
                '553' => 'Invalid PIN',
                '554' => 'System error',
                '555' => 'Ineligible transaction',
                '556' => 'Ineligible account',
                '557' => 'Transaction not supported',
                '558' => 'Restricted Card',
                '559' => 'Insufficient funds',
                '560' => 'Uses limit exceeded',
                '561' => 'Withdrawal limit would be exceeded',
                '562' => 'PIN tries limit was reached',
                '563' => 'Withdrawal limit already reached',
                '564' => 'Credit amount limit',
                '565' => 'No statement information',
                '566' => 'Statement not available',
                '567' => 'Invalid amount',
                '568' => 'External decline',
                '569' => 'No sharing',
                '571' => 'Contact card issuer',
                '572' => 'Destination not available',
                '573' => 'Routing error',
                '574' => 'Format error',
                '575' => 'External decline special condition',
                '580' => 'Bad CVV',
                '581' => 'Bad CVV2',
                '582' => 'Invalid transaction',
                '583' => 'PIN tries limit was exceeded',
                '584' => 'Bad CAVV',
                '585' => 'Bad ARQC',
            ],
            'ua' => [
                '000' => '',
                '001' => '',
                '003' => 'Транзакція виконана, необхідна додаткова ідентифікація',
                '007' => 'Адміністративна транзакція виконана успішно',
                '050' => 'Фінансова транзакція не виконана',
                '020' => 'Код помилки - 20. Невідома системна помилка.',
                '051' => 'Картка клієнта прострочена',
                '052' => 'Перевищено кількосты спроб вводу PIN',
                '053' => 'Не вдалось маршрутизувати транзакцію',
                '055' => 'Транзакція має некоректні атрибути або дана операція не дозволена',
                '056' => 'Операція, що запитується не підтримується хостом',
                '057' => 'Картка клієнта має статус «втрачена» або «вкрадена»',
                '058' => 'Картка клієнта має неправильний статус',
                '059' => 'Картка клієнта має обмежені можливості',
                '060' => 'Не знайдений вендор з вказаним номером рахунку',
                '061' => 'Невірна кількість інформаційних полів для заданого вендора',
                '062' => 'Невірний формат інформаційного поля платежа',
                '063' => 'Не знайдений prepaid-код',
                '064' => 'Track2 карти клієнта містить невірну інформацію',
                '069' => 'Невірний формат повідомлення',
                '074' => 'Неможливо авторизувати',
                '075' => 'Невірний PAN карти',
                '076' => 'На рахунку не вистачає коштів',
                '078' => 'Сталося дублювання транзакцій',
                '082' => 'Перевищення кількості використань карти клієнта',
                '085' => 'Неможливо видати баланс',
                '095' => 'Перевищення ліміту по сумі',
                '100' => 'Неможливо провести транзакцію',
                '101' => 'Неможливо авторизувати – необхідно зателефонувати емітенту карти',
                '105' => 'Даний тип карт не підтримується',
                '200' => 'Неправильний рахунок клієнта',
                '201' => 'Неправильний PIN',
                '205' => 'Некоректна сума',
                '209' => 'Невірний код транзакції',
                '210' => 'Невірне значення CAVV',
                '211' => 'Невірне значення CVV2',
                '212' => 'Не знайдено оригінальної транзакції для сліпу',
                '213' => 'Сліп пприймається повторно',
                '800' => 'Помилка формату',
                '801' => 'Не знайдено оригінальної транзакції для реверсу',
                '809' => 'Невірна операція закриття періоду',
                '810' => 'Відбувся тайм-аут',
                '811' => 'Системна помилка',
                '820' => 'Неправильний ідентифікатор терміналу',
                '880' => 'Було послано останній пакет - провантаження успішно завершено',
                '881' => 'Попередній етап провантаження був успішно виконаний –маються ще дані',
                '882' => 'Првантаження терміналу зупинено. Необхідно зателефонувати в процесинговий центр',
                '897' => 'Отримана невірна криптограма в транзакції',
                '898' => 'Отримано невірний MAC',
                '899' => 'Помилка синхронизації',
                '900' => 'Перевищено кількість спроб вводу PIN. Необхідний захват карти.',
                '901' => 'Картка прострочена, необхідний захват картки.',
                '909' => 'Необхідний захват картки',
                '959' => 'Адміністративна транзакція не підтримується',
                '-1'  => 'Системна помилка. POS не відповідає',
                '501' => 'Транзакція виконана успішно',
                '502' => 'Транзакція виконана успішно на часткову суму',
                '503' => 'Транзакція виконана успішно тілько на суму покупки (для транзакції 118 – Purchase with Cashback)',
                '510' => 'Немає номеру рахунку в запиті транзакції, є дккілька рахунків вказаного типу, і термінал підтримує вибір рахунку',
                '511' => 'Необхідно змінити PVV (дозволена тільки транзакція PIN Change)',
                '512' => 'Необхідно підтвердити результати перевірки платежа в системе online-прийому платежів',
                '513' => 'Транзакція запиту списка повідомлень виконана успішно.',
                '514' => ' Необхідно підтвердити результати попередньої перевірки операції',
                '515' => 'Не знайдена оригінальна транзакція',
                '516' => 'Сліп уже прийнято',
                '517' => 'Помилка в реквізитах платежа',
                '520' => 'Неможливо захопити Prepaid-код',
                '521' => 'Баланс кор. рахунку вичерпано',
                '522' => 'Перевищено еквайринговий ліміт',
                '524' => 'Вичерпано термін дії динамічного PVV',
                '525' => 'Слабкий PIN',
                '540' => 'Картка загублена',
                '541' => 'Картка вкрадена',
                '549' => 'Неприпустимий тип платежа для даного вендора',
                '550' => 'Несанкціоноване використання',
                '551' => 'Вичерпано термін дії картки',
                '552' => 'Невірна картка',
                '553' => 'Невірний PIN-код',
                '554' => 'Системна помилка',
                '555' => 'Невідповідна транзакція',
                '556' => 'Невідповідний рахунок',
                '557' => 'Транзакція не підтримується',
                '558' => 'Картка обмежена (дана операція по карті не дозволена)',
                '559' => 'Недостатньо коштів на рахунку',
                '560' => 'Перевищено ліміт на кількість використань картки',
                '561' => 'Ліміт на зняття готівки буде перевищено',
                '562' => 'Досягнуто або перевищено ліміт на кількість невірних вводів PIN-коду',
                '563' => 'Досягнуто ліміт на зняття готівки',
                '564' => 'Досягнуто ліміт на депозит',
                '565' => 'Немає інформації для надання звіту за рахунком',
                '566' => 'Надання звіту за рахунком неможливо (заборонено)',
                '567' => 'Недопустима сума',
                '568' => 'Транзакція відхилена зовнішнім хостом',
                '569' => 'Неузгоджений запит (дана картка не обслуговується в даному терміналі)',
                '571' => 'Необхідно звернутись до емітента',
                '572' => 'Авторизатор недоступний',
                '573' => 'Помилка маршрутизації',
                '574' => 'Помилка формату',
                '575' => 'Транзакція відхилена зовнішнім хостом за спеціальною умовою (власник картки підозрюється в шахрайстві)',
                '580' => 'Невірний CVV',
                '581' => 'Невірний CVV2',
                '582' => 'Невірна транзакція (транзакція не дозволена з такими умовами проведення)',
                '583' => 'Ліміт на кількість невірних вводів PIN-коду ВЖЕ досягнуто (тобто раніше було досягнуто ліміт на кількість невірних вводів PIN-коду, після чого було введено вірний PIN)',
                '584' => 'Невірне значення перевірочного числа 3D Secure Cardholder Authentication Verification Value',
                '585' => 'Невірне значення криптограми ARQC',
            ],
        ];

        if (isset($arr['ua'][$trancode])) {
            return $arr['ua'][$trancode];
        }
        return '';
    }
}
