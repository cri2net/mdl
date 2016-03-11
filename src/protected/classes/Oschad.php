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
        'preauth' => 0,
        'auth' => 1,
        'complete' => 21,
        'revers' => 24
    ];

    public static function logPaysys($paysys_name, $title, $text)
    {
        $mess = date('d-m-Y H:i:s').' -- '.USER_REAL_IP."\r\n";
        $mess .= $title.":\r\n".$text."\r\n======\r\n";
        $log_folder = ROOT . "/protected/logs/paysystems/$paysys_name/";
        if (!file_exists($log_folder)) {
            mkdir($log_folder, 0755, true);
        }
        $handle = fopen($log_folder . "$paysys_name.txt", 'a+');
        fwrite($handle, $mess);
        fclose($handle);
    }

    public static function getRCDesciption($rc)
    {
        $not_valid_answer_code = "ошибка, недопустимый код ответа";

        $osc_err_desc = [
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

            '00' => 'успешно',

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
            '45' => $not_valid_answer_code,
            '46' => $not_valid_answer_code,
            '47' => $not_valid_answer_code,
            '48' => $not_valid_answer_code,
            '49' => $not_valid_answer_code,
            '50' => $not_valid_answer_code,
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
            '69' => $not_valid_answer_code,
            '70' => $not_valid_answer_code,
            '71' => $not_valid_answer_code,
            '72' => $not_valid_answer_code,
            '73' => $not_valid_answer_code,
            '74' => $not_valid_answer_code,
            '75' => 'отказ, превышено количество попыток ввода ПИН',
            '76' => 'отказ, неверный ПИН, превышено кол-во попыток ввода ПИН',
            '77' => $not_valid_answer_code,
            '78' => $not_valid_answer_code,
            '79' => 'ошибка, уже возвращено',
            '80' => 'отказ, ошибка авторизационной сети',
            '81' => 'отказ, ошибка внешней сети',
            '82' => 'отказ, таймаут сети связи/ неверный CVV',
            '83' => 'отказ, ошибка операции',
            '84' => 'отказ, превышено время преавторизации',
            '85' => 'отказ, нужна проверка счета',
            '86' => 'отказ, проверка ПИН невозможна',
            '87' => $not_valid_answer_code,
            '89' => 'отказ, ошибка аутентификации',
            '90' => 'отказ, повторите через какое-то время',
            '91' => 'отказ, эмитент или узел комутации недоступен',
            '92' => 'отказ, невозможна адресация запроса',
            '93' => 'отказ, нарушение закона',
            '94' => 'отказ, повторный запрос',
            '95' => 'отказ, ошибка согласования',
            '96' => 'отказ, ошибка работы системы',
            '97' => $not_valid_answer_code,
            '98' => $not_valid_answer_code,
            '98' => $not_valid_answer_code
        ];

        if (isset($osc_err_desc[$rc])) {
            return $osc_err_desc[$rc];
        }
        return 'unknown error '.$rc;
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
        /*
        $this->fields['CURRENCY'] = $CURRENCY;
        $this->fields['MERCH_NAME'] = $MERCH_NAME;
        $this->fields['MERCH_URL'] = $MERCH_URL;
        $this->fields['MERCHANT'] = $MERCHANT;
        $this->fields['TERMINAL'] = $TERMINAL;
        $this->fields['EMAIL'] = $EMAIL;
        $this->fields['COUNTRY'] = $COUNTRY;
        $this->fields['MERCH_GMT'] = $MERCH_GMT;
        $this->fields['BACKREF'] = $BACKREF;
        */
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
        $this->fields['NONCE'] = md5(time().'hvji87.}%3@3*6hg');
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
        $this->fields['NONCE'] = md5(microtime().'hvji87.}%3@');
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
        $this->fields['P_SIGN'] = hash_hmac('sha1',$text,$key);
        $this->last_mac_str = $text;
    }

    public function get_html_fields($test_mode = false)
    {
        $ftype = ($test_mode)?'text':'hidden';
        $fcount = count($this->fields_order[$this->trtype_str]);
        $text = '';
        for ($i=0; $i<$fcount; $i++) {
            $fname = $this->fields_order[$this->trtype_str][$i];
            $text .= '<input type="'.$ftype.'" name="'.$fname.'" value="'.$this->fields[$fname].'">';
        }
        $text .= '<input type="'.$ftype.'" name="P_SIGN" value="'.$this->fields['P_SIGN'].'">';

        if ($test_mode) {
            $text .= '<input type="'.$ftype.'" value="'.$this->last_mac_str.'">';
            $text .= '<br><input type="submit" value="submit">';
        }
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

    /*
    public function check_sign($key_hex){
    $fcount = count($this->fields_order[$this->trtype_str]);
    $text = '';
    $trtype_str = $this->trtype_by_code($this->fields['TRTYPE']);
    for($i=0; $i<$fcount; $i++){
    $fname = $this->fields_order[$trtype_str][$i];
    $text .= mb_strlen( $this->fields[$fname]) . $this->fields[$fname];
    }
    $key = pack('H*', $key_hex);
    return ($this->fields['P_SIGN'] == hash_hmac('sha1',$text,$key));
    }
    */
}
