<?php

class Oschad
{
    private $fields = [];
    private $trtype_str = '';
    private $last_mac_str = '';

    private $fields_order = [
        'preauth'  => ['AMOUNT', 'CURRENCY', 'ORDER', 'DESC', 'MERCH_NAME', 'MERCH_URL', 'MERCHANT', 'TERMINAL', 'EMAIL', 'TRTYPE', 'COUNTRY', 'MERCH_GMT', 'TIMESTAMP', 'NONCE', 'BACKREF'],
        'auth'     => ['AMOUNT', 'CURRENCY', 'ORDER', 'DESC', 'MERCH_NAME', 'MERCH_URL', 'MERCHANT', 'TERMINAL', 'EMAIL', 'TRTYPE', 'COUNTRY', 'MERCH_GMT', 'TIMESTAMP', 'NONCE', 'BACKREF'],
        'revers'   => ['ORDER', 'ORG_AMOUNT', 'AMOUNT', 'CURRENCY', 'RRN', 'INT_REF', 'TRTYPE', 'TERMINAL', 'BACKREF', 'TIMESTAMP', 'NONCE'],
        'complete' => ['ORDER', 'AMOUNT', 'CURRENCY', 'RRN', 'INT_REF', 'TRTYPE', 'TERMINAL', 'BACKREF', 'TIMESTAMP', 'NONCE'],
    ];

    private $trans_types = [
        'preauth'  => 0,
        'auth'     => 1,
        'complete' => 21,
        'revers'   => 24,
    ];

    public static function logPaysys($paysys_name, $title, $text)
    {
        $mess = date('d-m-Y H:i:s').' -- '.USER_REAL_IP."\r\n";
        $mess .= $title.":\r\n".$text."\r\n======\r\n";
        $log_folder = PROTECTED_DIR . "/logs/paysystems/$paysys_name/";
        if (!file_exists($log_folder)) {
            mkdir($log_folder, 0755, true);
        }
        $handle = fopen($log_folder . "$paysys_name.txt", 'a+');
        fwrite($handle, $mess);
        fclose($handle);
    }

    public static function getErrorDescription($trancode)
    {
        $arr = [
            'ru' => [
                '-1' => 'обязательное поле не заполнено',
                '-2' => 'запрос не соответствует спецификации',
                '-3' => 'комуникац.сервер не отвечает или неверный формат файла ответа',
                '-4' => 'нет связи с комуникац. сервером',
                '-5' => 'не настроена связь с комуникац. сервером',
                '-6' => 'ошибка настройки e-Gateway',
                '-7' => 'ошибочный ответ от комуникац. сервера',
                '-8' => 'ошибка в поле номера карты',
                '-9' => 'ошибка в поле срока карты',
                '-10' => 'ошибка в поле суммы',
                '-11' => 'ошибка в поле валюты',
                '-12' => 'ошибка в поле MerchantID',
                '-13' => 'неожиданный ip-адрес',
                '-15' => 'ошибка в поле RRN',
                '-16' => 'терминал временно заблокирован',
                '-17' => 'доступ запрещен',
                '-18' => 'ошибка в CVV или CVC',
                '-19' => 'ошибка аутентификации',
                '-20' => 'превышено время проведения транзакции',
                '-21' => 'дубликат транзакции',
                '-22' => 'ошибка аутентификации клиента',
                '00' => '',
                '01' => 'обратитесь к эмитенту карты',
                '02' => 'обратитесь к эмитенту карты - спец. условия',
                '03' => 'отказ, предприятие не принимает данный вид карт',
                '04' => 'отказ, карта заблокирована (restricted)',
                '05' => 'отказ, операция отклонена',
                '06' => 'ошибка, повторите запрос',
                '07' => 'отказ, карта заблокирована (disabled)',
                '08' => 'нужна доп. информация',
                '09' => 'запрос в процессе обработки',
                '10' => 'подтвердить для частичной суммы операции',
                '11' => 'подтвердить для VIP-персоны',
                '12' => 'отказ, неизвестный тип операции',
                '13' => 'некорректная сумма операции',
                '14' => 'отказ, карта не найдена',
                '15' => 'отказ, эмитент не существует',
                '16' => 'успешно, обновите третью дорожку карты',
                '17' => 'отказ, отказ пользователя',
                '18' => 'ошибка, недопустимый код ответа (customer dispute)',
                '19' => 'отказ, повторите операцию',
                '20' => 'ошибка, недопустимый код ответа (invalid response)',
                '21' => 'ошибка, недопустимый код ответа (no action taken)',
                '22' => 'ошибка в работе системы',
                '23' => 'отказ, неакцептованые затраты операции',
                '24' => 'ошибка, недопустимый код ответа (update not supported)',
                '25' => 'ошибка, недопустимый код ответа (no such record)',
                '26' => 'ошибка, недопустимый код ответа (dublicate update/replaced)',
                '27' => 'ошибка, недопустимый код ответа (field update error)',
                '28' => 'ошибка, недопустимый код ответа (file locked out)',
                '29' => 'ошибка, свяжитесь с центром обработки',
                '30' => 'отказ, ошибка в формате запроса',
                '31' => 'отказ, эмитент временно отключился',
                '32' => 'частичное окончание',
                '33' => 'отказ, срок действия карты истек',
                '34' => 'отказ, подозрение в машенничестве',
                '35' => 'отказ, предприятию связаться с эмитентом',
                '36' => 'отказ, карта заблокирована',
                '37' => 'отказ, свяжитесь со своим банком',
                '38' => 'отказ, превышено количество попыток ввода ПИН',
                '39' => 'отказ, кредитного счета нет',
                '40' => 'отказ, функция не поддерживается',
                '41' => 'отказ, карта утеряна',
                '42' => 'отказ, универсального счета нет',
                '43' => 'отказ, карта украдена',
                '44' => 'отказ, инвестиционного счета нет',
                '45' => 'ошибка, недопустимый код ответа',
                '46' => 'ошибка, недопустимый код ответа',
                '47' => 'ошибка, недопустимый код ответа',
                '48' => 'ошибка, недопустимый код ответа',
                '49' => 'ошибка, недопустимый код ответа',
                '50' => 'ошибка, недопустимый код ответа',
                '51' => 'отказ, недостаточно средств',
                '52' => 'отказ, чекового счета нет',
                '53' => 'отказ, сберегательного счета нет',
                '54' => 'отказ, истек срок действия карты',
                '55' => 'отказ, некорректный ПИН',
                '56' => 'отказ, отсутствуют данные о карте',
                '57' => 'отказ, операция запрещена',
                '58' => 'отказ, неизвестный тип карты',
                '59' => 'отказ, неверный CVC или срок действия карты',
                '60' => 'отказ, предприятию связаться с центром обработки',
                '61' => 'отказ, превышен лимит суммы операции',
                '62' => 'отказ, карточка блокирована',
                '63' => 'ошибка, нарушение безопасности системы',
                '64' => 'отказ, неверная оригинальная сумма операции',
                '65' => 'отказ, превышен лимит повторов операции',
                '66' => 'отказ, предприятию связаться с центром обработки',
                '67' => 'отказ, если операция в АТМ',
                '68' => 'отказ, нет ответа в отведенное время',
                '69' => 'ошибка, недопустимый код ответа',
                '70' => 'ошибка, недопустимый код ответа',
                '71' => 'ошибка, недопустимый код ответа',
                '72' => 'ошибка, недопустимый код ответа',
                '73' => 'ошибка, недопустимый код ответа',
                '74' => 'ошибка, недопустимый код ответа',
                '75' => 'отказ, превышено количество попыток ввода ПИН',
                '76' => 'отказ, неверный ПИН, превышено кол-во попыток ввода ПИН',
                '77' => 'ошибка, недопустимый код ответа',
                '78' => 'ошибка, недопустимый код ответа',
                '79' => 'ошибка, уже возвращено',
                '80' => 'отказ, ошибка авторизационной сети',
                '81' => 'отказ, ошибка внешней сети',
                '82' => 'отказ, таймаут сети связи/ неверный CVV',
                '83' => 'отказ, ошибка операции',
                '84' => 'отказ, превышено время преавторизации',
                '85' => 'отказ, нужна проверка счета',
                '86' => 'отказ, проверка ПИН невозможна',
                '87' => 'ошибка, недопустимый код ответа',
                '89' => 'отказ, ошибка аутентификации',
                '90' => 'отказ, повторите через какое-то время',
                '91' => 'отказ, эмитент или узел комутации недоступен',
                '92' => 'отказ, невозможна адресация запроса',
                '93' => 'отказ, нарушение закона',
                '94' => 'отказ, повторный запрос',
                '95' => 'отказ, ошибка согласования',
                '96' => 'отказ, ошибка работы системы',
                '97' => 'ошибка, недопустимый код ответа',
                '98' => 'ошибка, недопустимый код ответа',
                '98' => 'ошибка, недопустимый код ответа',
            ],
            'ua' => [
                '-1' => 'Обов’язкове поле не заповнено',
                '-2' => 'Запит не відповідає специфікації',
                '-3' => 'Комунікаційний сервер не відповідає або невірний формат файлу відповіді',
                '-4' => 'Немає зв’язку з комунікаційним сервером',
                '-5' => 'Не налаштовано зв’язок з комунікаційним сервером',
                '-6' => 'Помилка налаштування e-Gateway',
                '-7' => 'Помилкова відповідь від комунікаційного серверу',
                '-8' => 'Помилка у полі номера карти',
                '-9' => 'Помилка у полі терміну дії карти',
                '-10' => 'Помилка у полі суми',
                '-11' => 'Помилка у полі валюти',
                '-12' => 'Помилка у полі MerchantID',
                '-13' => 'Неочікувана IP-адреса',
                '-15' => 'Помилка у полі RRN',
                '-16' => 'Термінал тимчасово заблокований',
                '-17' => 'Доступ заборонено',
                '-18' => 'Помилка у CVV або CVC',
                '-19' => 'Помилка аутентифікації',
                '-20' => 'Перевищено час проведення транзакції',
                '-21' => 'Дублікат транзакції',
                '-22' => 'Помилка аутентифікації клієнта',
                '00' => '',
                '01' => 'Зверніться до емітента карти',
                '02' => 'Зверніться до емітента карти - спец. умови',
                '03' => 'Відмова, установа не приймає даний вид карт',
                '04' => 'Відмова, карта заблокована (restricted)',
                '05' => 'Відмова, операція відхилена',
                '06' => 'Помилка, повторіть запит',
                '07' => 'Відмова, карта заблокована (disabled)',
                '08' => 'Потрібна додаткова інформація',
                '09' => 'Запит в обробці',
                '10' => 'Підтвердити для часткової суми операції',
                '11' => 'Підтвердити для VIP-персони',
                '12' => 'Відмова, невідомий тип операції',
                '13' => 'Некоректна сума операції',
                '14' => 'Відмова, карта не знайдена',
                '15' => 'Відмова, емітент не існує',
                '16' => 'успешно, обновіть третю доріжку карти',
                '17' => 'Відмова, відмова користувача',
                '18' => 'Помилка, неприпустимий код відповіді (customer dispute)',
                '19' => 'Відмова, повторіть операцію',
                '20' => 'Помилка, неприпустимий код відповіді (invalid response)',
                '21' => 'Помилка, неприпустимий код відповіді (no action taken)',
                '22' => 'Помилка у роботі системи',
                '23' => 'Відмова, не схвалені витрати операції',
                '24' => 'Помилка, неприпустимий код відповіді (update not supported)',
                '25' => 'Помилка, неприпустимий код відповіді (no such record)',
                '26' => 'Помилка, неприпустимий код відповіді (dublicate update/replaced)',
                '27' => 'Помилка, неприпустимий код відповіді (field update error)',
                '28' => 'Помилка, неприпустимий код відповіді (file locked out)',
                '29' => 'Помилка, зв’яжіться з центром обробки',
                '30' => 'Відмова, помилка у форматі запиту',
                '31' => 'Відмова, емітент тимчасово відключився',
                '32' => 'Часткове закінчення',
                '33' => 'Відмова, термін дії картки закінчився',
                '34' => 'Відмова, підозра у шахрайстві',
                '35' => 'Відмова, установі зв’язатися з емітентом',
                '36' => 'Відмова, карта заблокована',
                '37' => 'Відмова, зв’яжіться зі своїм банком',
                '38' => 'Відмова, перевищено кількість спроб введення PIN',
                '39' => 'Відмова, кредитного рахунку немає',
                '40' => 'Відмова, функція не підтримується',
                '41' => 'Відмова, карта загублена',
                '42' => 'Відмова, універсального рахунку немає',
                '43' => 'Відмова, карта вкрадена',
                '44' => 'Відмова, інвестиційного рахунку немає',
                '45' => 'Помилка, неприпустимий код відповіді',
                '46' => 'Помилка, неприпустимий код відповіді',
                '47' => 'Помилка, неприпустимий код відповіді',
                '48' => 'Помилка, неприпустимий код відповіді',
                '49' => 'Помилка, неприпустимий код відповіді',
                '50' => 'Помилка, неприпустимий код відповіді',
                '51' => 'Відмова, недостатньо коштів',
                '52' => 'Відмова, чекового рахунку немає',
                '53' => 'Відмова, ощадного рахунку немає',
                '54' => 'Відмова, закінчився термін дії карти',
                '55' => 'Відмова, некоректний PIN',
                '56' => 'Відмова, відсутні дані про карту',
                '57' => 'Відмова, операція заборонена',
                '58' => 'Відмова, невідомий тип карти',
                '59' => 'Відмова, невірний CVC або термін дії карти',
                '60' => 'Відмова, установі зв’язатися з центром обробки',
                '61' => 'Відмова, перевищено ліміт суми операції',
                '62' => 'Відмова, картка заблокована',
                '63' => 'Помилка, порушення безпеки системи',
                '64' => 'Відмова, невірна оригінальна сума операції',
                '65' => 'Відмова, перевищено ліміт повторів операції',
                '66' => 'Відмова, установі зв’язатися з центром обробки',
                '67' => 'Відмова, якщо операція у АТМ',
                '68' => 'Відмова, немає відповіді у відведений час',
                '69' => 'Помилка, неприпустимий код відповіді',
                '70' => 'Помилка, неприпустимий код відповіді',
                '71' => 'Помилка, неприпустимий код відповіді',
                '72' => 'Помилка, неприпустимий код відповіді',
                '73' => 'Помилка, неприпустимий код відповіді',
                '74' => 'Помилка, неприпустимий код відповіді',
                '75' => 'Відмова, перевищено кількість спроб введення PIN',
                '76' => 'Відмова, невірний PIN, перевищено кількість спроб введення PIN',
                '77' => 'Помилка, неприпустимий код відповіді',
                '78' => 'Помилка, неприпустимий код відповіді',
                '79' => 'Помилка, вже повернуто',
                '80' => 'Відмова, помилка авторизаційної мережі',
                '81' => 'Відмова, помилка зовнішньої мережі',
                '82' => 'Відмова, таймаут мережі зв’язку / невірний CVV',
                '83' => 'Відмова, помилка операції',
                '84' => 'Відмова, перевищено час преавторізаціі',
                '85' => 'Відмова, потрібна перевірка рахунку',
                '86' => 'Відмова, перевірка PIN неможлива',
                '87' => 'Помилка, неприпустимий код відповіді',
                '89' => 'Відмова, помилка аутентифікації',
                '90' => 'Відмова, повторіть через якийсь час',
                '91' => 'Відмова, емітент або вузол комутації недоступний',
                '92' => 'Відмова, неможлива адресація запиту',
                '93' => 'Відмова, порушення закону',
                '94' => 'Відмова, повторний запит',
                '95' => 'Відмова, помилка узгодження',
                '96' => 'Відмова, помилка роботи системи',
                '97' => 'Помилка, неприпустимий код відповіді',
                '98' => 'Помилка, неприпустимий код відповіді',
                '98' => 'Помилка, неприпустимий код відповіді',
            ],
        ];

        if (isset($arr['ua'][$trancode])) {
            return $arr['ua'][$trancode];
        }
        return '';
    }

    public function trtype_by_code($trcode)
    {
        foreach ($this->trans_types as $key => $trc) {
            if ($trc == $trcode) {
                return $key;
            }
        }
        return 'unknown';
    }

    public function set_merchant($settings_array)
    {
        $this->fields = $settings_array;
    }

    public function set_order($AMOUNT, $ORDER, $DESC)
    {
        $this->fields['AMOUNT'] = $AMOUNT;
        $this->fields['ORDER'] = $ORDER;
        $this->fields['DESC'] = $DESC;
    }

    public function set_transaction($TRTYPE)
    {
        $this->fields['TRTYPE'] = $this->trans_types[$TRTYPE];
        $this->trtype_str = $TRTYPE;
        $this->fields['NONCE'] = md5(uniqid(rand(), 1));
        $this->fields['TIMESTAMP'] = gmdate('YmdHis');
    }

    public function set_reversal($ORDER, $AMOUNT, $RRN, $INT_REF)
    {
        $this->fields['ORDER'] = $ORDER;
        $this->fields['ORG_AMOUNT'] = $AMOUNT;
        $this->fields['AMOUNT'] = $AMOUNT;
        $this->fields['TRTYPE'] = '24';
        $this->fields['RRN'] = $RRN;
        $this->fields['INT_REF'] = $INT_REF;
        $this->trtype_str = 'revers';
        $this->fields['NONCE'] = md5(uniqid(rand(), 1));
        $this->fields['TIMESTAMP'] = gmdate('YmdHis');
    }

    public function sign($key_hex)
    {
        $fcount = count($this->fields_order[$this->trtype_str]);
        $text = '';
        for ($i=0; $i<$fcount; $i++) {
            $fname = $this->fields_order[$this->trtype_str][$i];
            $text .= mb_strlen($this->fields[$fname]) . $this->fields[$fname];
        }
        $key = pack('H*', $key_hex);
        $this->fields['P_SIGN'] = hash_hmac('sha1', $text, $key);
        $this->last_mac_str = $text;
    }

    public function get_html_fields()
    {
        $fcount = count($this->fields_order[$this->trtype_str]);
        $text = '';
        for ($i=0; $i<$fcount; $i++) {
            $fname = $this->fields_order[$this->trtype_str][$i];
            $text .= '<input type="hidden" name="'.$fname.'" value="'.$this->fields[$fname].'">';
        }
        $text .= '<input type="hidden" name="P_SIGN" value="'.$this->fields['P_SIGN'].'">';

        return $text;
    }

    public function get_fields()
    {
        return $this->fields;
    }

    public function parse_post($POST)
    {
        $this->fields = $POST;
    }

    public function revers_payment($pid)
    {
        global $oschad_merchant_settings, $oschad_sign_key, $payment_form_action;

        $_payment = PDO_DB::row_by_id(ShoppingCart::TABLE, $pid);
        if ($_payment === null) {
            return false;
        }

        $proc_data = (array)(@json_decode($_payment['processing_data']));
        $proc_data['requests'] = (array)$proc_data['requests'];
        $resp_date = $proc_data['dates'][count($proc_data['dates']-1)];
        $last_resp = $resp_data['requests'][$resp_date];
        $osc_first = $_payment['processing_data']['first'];

        $oschad_merchant_settings['BACKREF'] = 'https://www.gioc.kiev.ua/payment-status/'.((integer)$pid);
        $this->set_merchant($oschad_merchant_settings);
        $this->set_reversal($pid, $last_resp->AMOUNT, $last_resp->RRN, $last_resp->INT_REF);
        $this->sign($oschad_sign_key);

        $res = Http::HttpPost($payment_form_action, $this->fields, false);
        return $res;
    }
}
