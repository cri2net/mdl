<?php
class CronTasts
{
    public static function findBrokenEmails()
    {
        $hostname = '{mail.gioc:110/pop3/novalidate-cert}INBOX';
        $username = 'no.reply@gioc.kiev.ua';
        $password = 'N3p12Q';

        /* try to connect */
        $inbox = imap_open($hostname, $username, $password) or die('Cannot connect: ' . imap_last_error());
        $emails = imap_search($inbox, 'UNDELETED'); // grab emails

        if ($emails) {
            rsort($emails); // put the newest emails on top

            $pdo = PDO_DB::getPDO();
            $stm_upd = $pdo->prepare("UPDATE " . User::TABLE . " SET broken_email=1 WHERE email=? AND deleted=0 LIMIT 1");
            
            foreach ($emails as $email_number) {
                $overview = imap_fetch_overview($inbox, $email_number, 0);

                if (stristr($overview[0]->subject, '-- NOT SEND TO:')) {
                    $email = end(self::extractEmailAddress($overview[0]->subject));
                    $stm_upd->execute([$email]);
                    imap_delete($inbox, $email_number);
                }

                if (stristr($overview[0]->from, 'quarantine@i.ua')) {
                    // это письмо с просьбой перейти по ссылке, чтоб письмо попало во входящие
                    $message = imap_body($inbox, $email_number);
                    $links = self::extractLinks($message);
                    if (isset($links[0])) {
                        imap_delete($inbox, $email_number);
                        @file_get_contents($links[0]);
                    }
                }
            }
        }

        imap_expunge($inbox);
        imap_close($inbox); // close the connection
    }

    private static function extractEmailAddress($string)
    {
        foreach (preg_split('/\s/', $string) as $token) {
            $email = filter_var(filter_var($token, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
            if ($email !== false) {
                $emails[] = $email;
            }
        }
        return $emails;
    }

    private static function extractLinks($string)
    {
        $regex = "((https?|ftp)\:\/\/)?"; // SCHEME
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

        preg_match_all("/$regex/", $string, $matches); 

        return array_values(array_unique($matches[0]));
    }
}
