<?php

class Email
{
    const FOLDER = '/protected/email_templates';
    
    private $PHPMailer = null;

    public function __construct()
    {
        $this->PHPMailer = new PHPMailer();

        $this->CharSet     = 'UTF-8';
        $this->ContentType = "text/html";
        $this->From        = EMAIL_FROM;
        $this->FromName    = EMAIL_FROM_NAME;
        $this->ReturnPath  = EMAIL_FROM;
        $this->Hostname    = EMAIL_FROM;
        $this->AllowEmpty  = true;
        $this->XMailer     = ' ';
    }

    public function __set($name, $value)
    {
        if (isset($this->PHPMailer->$name)) {
            $this->PHPMailer->$name = $value;
        }
    }

    public function __call($name, $arguments)
    {
        if (is_callable('$this->PHPMailer->' . $name)) {
            call_user_func_array(array($this->PHPMailer, $name), $arguments);
        }
    }

    public function send($to, $subject, $message, $template = '', $data = array())
    {
        $message = (strlen($template) > 0) ? (self::get_template($template)) : $message;
        $message = self::fetch($message, $data);
        
        $this->Subject = $subject;
        $this->Body    = $message;
        
        if (is_array($to)) {
            call_user_func_array(array($this->PHPMailer, 'AddAddress'), $to);
        } else {
            $this->PHPMailer->AddAddress($to);
        }

        return $this->PHPMailer->Send();
    }

    public static function get_template($template)
    {
        $filename = ROOT . self::FOLDER . '/' . $template . '.tpl';

        if (file_exists($filename)) {
            return file_get_contents($filename);
        }

        throw new Exception("Email template not found");
        return '';
    }

    public static function fetch($template_text, $data = array())
    {
        self::replace_defaults($data);
        $re1 = '.*?'; // Non-greedy match on filler
        $re2 = '(\\{{([0-9a-z_-]+)\\}})'; // Curly Braces 1

        if (preg_match_all("/".$re1.$re2."/is", $template_text, $matches)) {
            for ($i=0; $i < count($matches[1]); $i++) {
                $template_text = str_replace($matches[1][$i], $data[$matches[2][$i]], $template_text);
            }
        }

        return $template_text;
    }

    public static function replace_defaults(&$data)
    {
        if (defined('SITE_DOMAIN') && !isset($data['site_domain'])) {
            $data['site_domain'] = SITE_DOMAIN;
        }
        if (defined('BASE_URL') && !isset($data['base_url'])) {
            $data['base_url'] = BASE_URL;
        }
    }

    public static function get_link($email)
    {
        $services = array(
            array('mail.ru', 'Почта Mail.Ru', 'https://e.mail.ru/'),
            array('bk.ru', 'Почта Mail.Ru (bk.ru)', 'https://e.mail.ru/'),
            array('list.ru', 'Почта Mail.Ru (list.ru)', 'https://e.mail.ru/'),
            array('inbox.ru', 'Почта Mail.Ru (inbox.ru)', 'https://e.mail.ru/'),
            array('yandex.ru', 'Яндекс.Почта', 'https://mail.yandex.ru/'),
            array('ya.ru', 'Яндекс.Почта', 'https://mail.yandex.ru/'),
            array('yandex.ua', 'Яндекс.Почта', 'https://mail.yandex.ua/'),
            array('yandex.by', 'Яндекс.Почта', 'https://mail.yandex.by/'),
            array('yandex.kz', 'Яндекс.Почта', 'https://mail.yandex.kz/'),
            array('yandex.com', 'Yandex.Mail', 'https://mail.yandex.com/'),
            array('gmail.com', 'Gmail', 'https://mail.google.com/'),
            array('googlemail.com', 'Gmail', 'https://mail.google.com/'),
            array('outlook.com', 'Outlook.com', 'https://mail.live.com/'),
            array('hotmail.com', 'Outlook.com (Hotmail)', 'https://mail.live.com/'),
            array('live.ru', 'Outlook.com (live.ru)', 'https://mail.live.com/'),
            array('live.com', 'Outlook.com (live.com)', 'https://mail.live.com/'),
            array('me.com', 'iCloud Mail', 'https://www.icloud.com/'),
            array('icloud.com', 'iCloud Mail', 'https://www.icloud.com/'),
            array('rambler.ru', 'Рамблер-Почта', 'https://mail.rambler.ru/'),
            array('yahoo.com', 'Yahoo! Mail', 'https://mail.yahoo.com/'),
            array('ukr.net', 'Почта ukr.net', 'https://mail.ukr.net/'),
            array('i.ua', 'Почта I.UA', 'http://mail.i.ua/'),
            array('bigmir.net', 'Почта Bigmir.net', 'http://mail.bigmir.net/'),
            array('tut.by', 'Почта tut.by', 'https://mail.tut.by/'),
            array('inbox.lv', 'Inbox.lv', 'https://www.inbox.lv/'),
            array('mail.kz', 'Почта mail.kz', 'http://mail.kz/')
        );
        
        list($user, $domain) = explode('@', $email);

        $domain = strtolower($domain);
        foreach ($services as $item) {
            if ($item[0] == $domain) {
                return array(
                    'domain' => $item[0],
                    'title' => $item[1],
                    'link' => $item[2]
                );
            }
        }

        return false;
    }
}
