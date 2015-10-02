<?php

class Email
{
    const FOLDER = '/protected/email_templates';
    
    private $PHPMailer = null;

    public function __construct()
    {
        $this->PHPMailer = new PHPMailer();

        $this->CharSet       = 'UTF-8';
        $this->ContentType   = "text/html";
        $this->From          = EMAIL_FROM;
        $this->FromName      = EMAIL_FROM_NAME;
        $this->ReturnPath    = EMAIL_FROM;
        $this->Hostname      = EMAIL_HOST;
        $this->AllowEmpty    = true;
        $this->XMailer       = ' ';
        $this->SMTPKeepAlive = true;
        

        //////////////////////////////////////////////////
        // блок настроек для отправки сообщений по SMTP //
        //////////////////////////////////////////////////
        
        // $this->isSMTP();

        // $this->Host       = 'localhost'; // SMTP hosts.
        // $this->Port       = 25; // The default SMTP server port.
        // $this->SMTPSecure = ''; // What kind of encryption to use on the SMTP connection. Options: '', 'ssl' or 'tls'
        // $this->SMTPAuth   = true;
        // $this->Username   = ''; // SMTP username.
        // $this->Password   = ''; // SMTP password.
    }

    public function __set($name, $value)
    {
        if (isset($this->PHPMailer->$name)) {
            $this->PHPMailer->$name = $value;
        }
    }

    public function __call($name, $arguments)
    {
        if (is_callable([$this->PHPMailer, $name])) {
            return call_user_func_array([$this->PHPMailer, $name], $arguments);
        }
    }

    public function send($to, $subject, $message, $template = '', $data = [])
    {
        $message = (strlen($template) > 0) ? (self::getTemplate($template)) : $message;
        $message = self::fetch($message, $data);
        
        $this->Subject = $subject;
        $this->Body    = $message;
        
        if (is_array($to)) {
            call_user_func_array([$this->PHPMailer, 'AddAddress'], $to);
        } else {
            $this->PHPMailer->AddAddress($to);
        }

        return $this->PHPMailer->Send();
    }

    public static function getTemplate($template)
    {
        $filename = ROOT . self::FOLDER . '/' . $template . '.tpl';

        if (file_exists($filename)) {
            return file_get_contents($filename);
        }

        throw new Exception("Email template not found");
        return '';
    }

    public static function fetch($template_text, $data = [])
    {
        self::replaceDefaults($data);
        $re1 = '.*?'; // Non-greedy match on filler
        $re2 = '(\\{{([0-9a-z_-]+)\\}})'; // Curly Braces 1

        if (preg_match_all("/".$re1.$re2."/is", $template_text, $matches)) {
            for ($i=0; $i < count($matches[1]); $i++) {
                $template_text = str_replace($matches[1][$i], $data[$matches[2][$i]], $template_text);
            }
        }

        return $template_text;
    }

    public static function replaceDefaults(&$data)
    {
        if (defined('SITE_DOMAIN') && !isset($data['site_domain'])) {
            $data['site_domain'] = SITE_DOMAIN;
        }
        if (defined('BASE_URL') && !isset($data['base_url'])) {
            $data['base_url'] = BASE_URL;
        }
    }

    public static function getLinkToService($email)
    {
        $lang = [
            'mail.ru'        => ['ru' => 'Почта Mail.Ru',            'ua' => 'Пошта Mail.Ru'],
            'bk.ru'          => ['ru' => 'Почта Mail.Ru (bk.ru)',    'ua' => 'Пошта Mail.Ru (bk.ru)'],
            'list.ru'        => ['ru' => 'Почта Mail.Ru (list.ru)',  'ua' => 'Пошта Mail.Ru (list.ru)'],
            'inbox.ru'       => ['ru' => 'Почта Mail.Ru (inbox.ru)', 'ua' => 'Пошта Mail.Ru (inbox.ru)'],
            'yandex.ru'      => ['ru' => 'Яндекс.Почта',             'ua' => 'Яндекс.Пошта'],
            'ya.ru'          => ['ru' => 'Яндекс.Почта',             'ua' => 'Яндекс.Пошта'],
            'yandex.ua'      => ['ru' => 'Яндекс.Почта',             'ua' => 'Яндекс.Пошта'],
            'yandex.by'      => ['ru' => 'Яндекс.Почта',             'ua' => 'Яндекс.Пошта'],
            'yandex.kz'      => ['ru' => 'Яндекс.Почта',             'ua' => 'Яндекс.Пошта'],
            'yandex.com'     => ['ru' => 'Yandex.Mail',              'ua' => 'Yandex.Mail'],
            'gmail.com'      => ['ru' => 'Gmail',                    'ua' => 'Gmail'],
            'googlemail.com' => ['ru' => 'Gmail',                    'ua' => 'Gmail'],
            'outlook.com'    => ['ru' => 'Outlook.com',              'ua' => 'Outlook.com'],
            'hotmail.com'    => ['ru' => 'Outlook.com (Hotmail)',    'ua' => 'Outlook.com (Hotmail)'],
            'live.ru'        => ['ru' => 'Outlook.com (live.ru)',    'ua' => 'Outlook.com (live.ru)'],
            'live.com'       => ['ru' => 'Outlook.com (live.com)',   'ua' => 'Outlook.com (live.com)'],
            'me.com'         => ['ru' => 'iCloud Mail',              'ua' => 'iCloud Mail'],
            'icloud.com'     => ['ru' => 'iCloud Mail',              'ua' => 'iCloud Mail'],
            'rambler.ru'     => ['ru' => 'Рамблер-Почта',            'ua' => 'Рамблер-Пошта'],
            'yahoo.com'      => ['ru' => 'Yahoo! Mail',              'ua' => 'Yahoo! Mail'],
            'ukr.net'        => ['ru' => 'Почта ukr.net',            'ua' => 'Пошта ukr.net'],
            'i.ua'           => ['ru' => 'Почта I.UA',               'ua' => 'Пошта I.UA'],
            'bigmir.net'     => ['ru' => 'Почта Bigmir.net',         'ua' => 'Пошта Bigmir.net'],
            'tut.by'         => ['ru' => 'Почта tut.by',             'ua' => 'Пошта tut.by'],
            'inbox.lv'       => ['ru' => 'Inbox.lv',                 'ua' => 'Inbox.lv'],
            'mail.kz'        => ['ru' => 'Почта mail.kz',            'ua' => 'Пошта mail.kz'],
        ];
        
        $services = [
            ['mail.ru',        'https://e.mail.ru/'],
            ['bk.ru',          'https://e.mail.ru/'],
            ['list.ru',        'https://e.mail.ru/'],
            ['inbox.ru',       'https://e.mail.ru/'],
            ['yandex.ru',      'https://mail.yandex.ru/'],
            ['ya.ru',          'https://mail.yandex.ru/'],
            ['yandex.ua',      'https://mail.yandex.ua/'],
            ['yandex.by',      'https://mail.yandex.by/'],
            ['yandex.kz',      'https://mail.yandex.kz/'],
            ['yandex.com',     'https://mail.yandex.com/'],
            ['gmail.com',      'https://mail.google.com/'],
            ['googlemail.com', 'https://mail.google.com/'],
            ['outlook.com',    'https://mail.live.com/'],
            ['hotmail.com',    'https://mail.live.com/'],
            ['live.ru',        'https://mail.live.com/'],
            ['live.com',       'https://mail.live.com/'],
            ['me.com',         'https://www.icloud.com/'],
            ['icloud.com',     'https://www.icloud.com/'],
            ['rambler.ru',     'https://mail.rambler.ru/'],
            ['yahoo.com',      'https://mail.yahoo.com/'],
            ['ukr.net',        'https://mail.ukr.net/'],
            ['i.ua',           'http://mail.i.ua/'],
            ['bigmir.net',     'http://mail.bigmir.net/'],
            ['tut.by',         'https://mail.tut.by/'],
            ['inbox.lv',       'https://www.inbox.lv/'],
            ['mail.kz',        'http://mail.kz/'],
        ];
        
        list($user, $domain) = explode('@', $email);

        $domain = strtolower($domain);
        foreach ($services as $item) {
            if ($item[0] == $domain) {
                return [
                    'domain' => $item[0],
                    'title' => $lang[$item[0]]['ua'],
                    'link' => $item[1]
                ];
            }
        }

        return false;
    }
}
