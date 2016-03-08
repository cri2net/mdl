<?php

class EmailCron
{
    const TABLE = DB_TBL_EMAIL_CRON;
    const STREAM_COUNT = 5;
    public $email_in_connection = 200;
    protected $inline_attachments = [];

    public function checkCloseCron($cron_id)
    {
        $pdo = PDO_DB::getPDO();
        $table = TABLE_PREFIX . 'email_cron_part';
        $stm = $pdo->prepare("SELECT * FROM $table WHERE status<>'complete' AND cron_id=? LIMIT 1");
        $stm->execute([$cron_id]);

        if ($stm->fetch() === false) {
            PDO_DB::update(['status' => 'complete'], self::TABLE, $cron_id);
        }
    }

    public function getCronRecord(&$cron_part)
    {
        $pdo = PDO_DB::getPDO();
        $table = TABLE_PREFIX . 'email_cron_part';
        $stm = $pdo->query("SELECT COUNT(*) FROM $table WHERE status='sending'");
        $count = $stm->fetchColumn();

        if ($count >= self::STREAM_COUNT) {
            return null;
        }

        $cron_part = PDO_DB::first($table, "status='new'");
        if (!$cron_part) {
            return null;
        }

        return PDO_DB::row_by_id(self::TABLE, $cron_part['cron_id']);
    }

    private function checkCronIsActive($statement, $id)
    {
        $statement->execute([$cron_part['id']]);
        $tmp = $statement->fetch();
        if ($tmp['status'] == 'pause') {
            throw new Exception("Stoped by adminpanel");
        }
    }

    public function cron()
    {
        $cron = $this->getCronRecord($cron_part);
        if (!$cron) {
            return;
        }
        $table_part = TABLE_PREFIX . 'email_cron_part';
        $start_user_id = $cron_part['start_user_id'];
        $finish_user_id = $cron_part['finish_user_id'];
        $start_time = microtime(true);
        PDO_DB::update(['status' => 'sending', 'updated_at' => microtime(true)], self::TABLE, $cron['id']);
        PDO_DB::update(['status' => 'sending', 'updated_at' => microtime(true)], $table_part, $cron_part['id']);

        try {
            $pdo = PDO_DB::getPDO();
            $email = new Email();
            $email_in_connection = 0;
            // $email->SMTPDebug = 1;
            // $email->Debugoutput = 'error_log';

            $additional = @json_decode($cron['additional']);
            if ($additional != null) {
                if ($additional->email_from) {
                    $email->From = $additional->email_from;
                }
                if ($additional->email_from_name) {
                    $email->FromName = $additional->email_from_name;
                }
                if ($additional->email_return_path) {
                    $email->ReturnPath = $additional->email_return_path;
                }
            }

            $stm_update            = $pdo->prepare("UPDATE $table_part SET updated_at=?, start_user_id=? WHERE id=? LIMIT 1");
            $stm_update_count      = $pdo->prepare("UPDATE " . self::TABLE . " SET send_email=send_email+1 WHERE id=? LIMIT 1");
            $stm_update_count_part = $pdo->prepare("UPDATE $table_part         SET send_email=send_email+1 WHERE id=? LIMIT 1");
            $stm_count_part_get    = $pdo->prepare("SELECT * FROM $table_part WHERE id=? LIMIT 1");

            if ($cron['type'] == 'invoice') {
                $stm = $pdo->prepare("SELECT * FROM " . Flat::USER_FLATS_TABLE . " WHERE notify=1 AND user_id>=? AND user_id<=? ORDER BY user_id ASC");
                $stm->execute([$start_user_id, $finish_user_id]);

                $curr_user = ['id' => 0];

                while ($row = $stm->fetch()) {
                    
                    if ($row['user_id'] != $curr_user['id']) {
                        $curr_user = User::getUserById($row['user_id']);
                        $start_user_id = $curr_user['id'];
                        $stm_update->execute([microtime(true), $start_user_id, $cron_part['id']]);
                        $this->checkCronIsActive($stm_count_part_get, $cron_part['id']);
                    }

                    if (!$curr_user || $curr_user['broken_email']) {
                        continue;
                    }

                    $hash1 = Authorization::get_auth_hash1($curr_user['id']);
                    $hash2 = Authorization::get_auth_hash2($curr_user['id'], $hash1);

                    $online_version = BASE_URL . '/invoice/?uid=' . $curr_user['id'] . '&f=' . $row['id'] . '&hash2=' . $hash2;
                    $url = $online_version . '&email_mode=1';
                    $content = Http::HttpGet($url, false, false);

                    $replace = ($additional->info_block) ? "<br><br>{$additional->info_block}" : '';
                    $content = str_ireplace('<!-- {{infoblock}} -->', $additional->info_block, $content);
                    
                    if (strlen($content) > 250) {
                        $address = Flat::getAddressString($row['flat_id'], $row['city_id']);
                        $subject = str_replace('{ADDRESS}', $address, $cron['subject']);
                        $plain_text = Http::HttpGet($online_version . '&text_mode', false, false);
                        
                        if (!$plain_text) {
                            $plain_text = null;
                        } else {
                            $replace = ($additional->info_block) ? (strip_tags($additional->info_block) . "\r\n\r\n") : '';
                            $plain_text = str_ireplace('{{infoblock}}', $replace, $plain_text);
                        }

                        $mailSent = $this->sendInvoiceFromCron(
                            $email,
                            $curr_user['email'],
                            "{$curr_user['name']} {$curr_user['fathername']}",
                            $subject,
                            $content,
                            $plain_text,
                            $online_version
                        );

                        $stm_update_count->execute([$cron['id']]);
                        $stm_update_count_part->execute([$cron_part['id']]);

                        if ($mailSent) {
                            $email_in_connection++;
                            echo date('Y.m.d H:i:s '), "TO: {$curr_user['email']}, user_id={$row['user_id']}, user_flat_id={$row['id']}\r\n";
                        } else {
                            echo date('Y.m.d H:i:s '), "ERROR TO: {$curr_user['email']}, user_id={$row['user_id']}, user_flat_id={$row['id']}", $email->ErrorInfo, "\r\n";
                        }

                        if ($email_in_connection >= $this->email_in_connection) {
                            $email->smtpClose();
                            $email_in_connection = 0;
                        }
                    }
                }
            } elseif ($cron['type'] == 'newsletter_for_subscribers') {
                $stm = $pdo->prepare("SELECT * FROM " . User::SUBSCRIBE_TABLE . " WHERE subscribe=1 AND broken_email=0 AND id>=? AND id<=? ORDER BY id ASC");
                $stm->execute([$start_user_id, $finish_user_id]);

                while ($row = $stm->fetch()) {
                    
                    $start_user_id = $row['id'];
                    $stm_update->execute([microtime(true), $start_user_id, $cron_part['id']]);
                    $this->checkCronIsActive($stm_count_part_get, $cron_part['id']);

                    $email->clearAttachments();
                    $email->clearAllRecipients();
                    $email->clearCustomHeaders();
                    
                    $message = $this->loadStaticAttach($email, $cron['content']);
                    $message = $email->wrapText($message, 80);

                    $email->addCustomHeader('Precedence', 'bulk');
                    $email->AddAddress($row['email']);

                    $email->Body = $message;
                    if (!empty($cron['plain_text'])) {
                        $email->AltBody = $email->wrapText($email->normalizeBreaks($cron['plain_text']), 80);
                    } else {
                        $email->AltBody = $email->wrapText($email->normalizeBreaks($email->html2text($message)), 80);
                    }
                    $email->Subject = $cron['subject'];


                    $mailSent = false;
                    $shots = 5;
                    while (($shots >= 0) && !$mailSent) {
                        $mailSent = $email->call_phpmailer_send();
                        $shots--;
                    }

                    if ($mailSent) {
                        $email_in_connection++;
                        echo date('Y.m.d H:i:s '), "TO: {$row['email']}, subscriber_id={$row['id']}\r\n";
                    } else {
                        echo date('Y.m.d H:i:s '), "ERROR TO: {$row['email']}, subscriber_id={$row['id']}", $email->ErrorInfo, "\r\n";
                    }

                    if ($email_in_connection >= $this->email_in_connection) {
                        $email->smtpClose();
                        $email_in_connection = 0;
                    }

                    $stm_update_count->execute([$cron['id']]);
                    $stm_update_count_part->execute([$cron_part['id']]);
                }
            } elseif ($cron['type'] == 'newsletter') {
                $stm = $pdo->prepare("SELECT * FROM " . User::TABLE . " WHERE notify_email=1 AND broken_email=0 AND deleted=0 AND id>=? AND id<=? ORDER BY id ASC");
                $stm->execute([$start_user_id, $finish_user_id]);

                while ($row = $stm->fetch()) {
                    
                    $start_user_id = $row['id'];
                    $stm_update->execute([microtime(true), $start_user_id, $cron_part['id']]);
                    $this->checkCronIsActive($stm_count_part_get, $cron_part['id']);

                    $email->clearAttachments();
                    $email->clearAllRecipients();
                    $email->clearCustomHeaders();

                    $hash_1 = Authorization::get_auth_hash1($row['id']);
                    $hash_2 = Authorization::get_auth_hash2($row['id'], $hash_1);
                    $message = $cron['content'];
                    $message = str_replace('{{user_id}}', $row['id'], $message);
                    $message = str_replace('{{hash2}}', $hash_2, $message);
                    
                    $message = $this->loadStaticAttach($email, $message);
                    $message = $email->wrapText($message, 80);

                    $email->addCustomHeader('Precedence', 'bulk');
                    $email->AddAddress($row['email'], trim(htmlspecialchars("{$row['name']} {$row['fathername']}")));

                    $email->Body = $message;
                    if (!empty($cron['plain_text'])) {
                        $email->AltBody = $email->wrapText($email->normalizeBreaks($cron['plain_text']), 80);
                    } else {
                        $email->AltBody = $email->wrapText($email->normalizeBreaks($email->html2text($message)), 80);
                    }

                    $email->Subject = $cron['subject'];
                    
                    $mailSent = false;
                    $shots = 10;
                    while (($shots >= 0) && !$mailSent) {
                        $mailSent = $email->call_phpmailer_send();
                        $shots--;
                    }
                    if ($mailSent) {
                        $email_in_connection++;
                        echo date('Y.m.d H:i:s '), "TO: {$row['email']}, user_id={$row['id']}\r\n";
                    } else {
                        echo date('Y.m.d H:i:s '), "ERROR TO: {$row['email']}, user_id={$row['id']}", $email->ErrorInfo, "\r\n";
                    }

                    if ($email_in_connection >= $this->email_in_connection) {
                        $email->smtpClose();
                        $email_in_connection = 0;
                    }

                    $stm_update_count->execute([$cron['id']]);
                    $stm_update_count_part->execute([$cron_part['id']]);
                }
            }

            $update = [
                'status' => 'complete',
                'start_user_id' => $start_user_id,
                'updated_at' => microtime(true)
            ];
            PDO_DB::update($update, $table_part, $cron_part['id']);
            $this->checkCloseCron($cron['id']);

        } catch (Exception $e) {

            $update = [
                'status' => 'new',
                'updated_at' => microtime(true)
            ];
            PDO_DB::update($update, self::TABLE, $cron['id']);

            $update = [
                'status' => 'new',
                'start_user_id' => $start_user_id,
                'updated_at' => microtime(true)
            ];
            PDO_DB::update($update, $table_part, $cron_part['id']);

            echo $e->getMessage();
        }
    }

    public function sendInvoiceFromCron($email_object, $to, $to_username, $subject, $message, $AltBody = null, $online_version = null)
    {
        $email_object->clearAttachments();
        $email_object->clearAllRecipients();
        $email_object->clearCustomHeaders();
        
        $this->inline_attachments = [];
        $message = $this->loadStaticAttach($email_object, $message);
        $message = $email_object->wrapText($message, 80);

        $email_object->addCustomHeader('Precedence', 'bulk');
        $email_object->AddAddress($to, trim($to_username));

        $email_object->Body = $message;
        
        if ($AltBody === null) {
            $email_object->AltBody = $email_object->normalizeBreaks($email_object->html2text($email_object->Body));
            if ($online_version !== null) {
                $email_object->AltBody .= "\r\n\r\nOnline version: " . $online_version;
            }
            $email_object->AltBody = $email_object->wrapText($email_object->AltBody, 80);
        } else {
            $email_object->AltBody = $AltBody;
        }

        $email_object->Subject = $subject;

        $mailSent = false;
        $shots = 5;
        while (($shots >= 0) && !$mailSent) {
            $mailSent = $email_object->call_phpmailer_send();
            $shots--;
        }

        return $mailSent;
    }

    private function loadStaticAttach($email_object, $message)
    {
        preg_match_all("/(src|background)=[\"'](.*)[\"']/Ui", $message, $images);
        if (isset($images[2])) {
            foreach ($images[2] as $imgindex => $url)
                if (preg_match('#^[A-z]+://#', $url)) {
                    $need_replace = false;
                    $cid = md5($url);
                    $filename = PHPMailer::mb_pathinfo($url, PATHINFO_BASENAME);
                    $img = @file_get_contents($url);
                    
                    if ($img !== false) {
                        if ($email_object->addStringEmbeddedImage($img, $cid, $filename, 'base64', 'application/octet-stream', 'inline')) {
                            $need_replace = true;
                        }
                        if ($need_replace) {
                            $message = preg_replace("/".$images[1][$imgindex]."=[\"']".preg_quote($url, '/')."[\"']/Ui", $images[1][$imgindex]."=\"cid:".$cid."\"", $message);
                            $this->inline_attachments[$cid] = array('url' => $url, 'content' => base64_encode($img), 'filename'=> $filename, 'type' => PHPMailer::_mime_types(PHPMailer::mb_pathinfo($url, PATHINFO_EXTENSION)));
                        }
                    }
                }
        }

        return $message;
    }
}
