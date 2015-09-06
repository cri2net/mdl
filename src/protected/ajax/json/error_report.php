<?php
    $insert = array(
        'url' => stripslashes($_POST['url']),
        'raw_data' => json_encode($_POST),
        'timestamp' => microtime(true)
    );

    $error_id = PDO_DB::insert($insert, TABLE_PREFIX . 'text_errors');
    

    $raw_data = json_decode($insert['raw_data']);

    $text_pre = htmlspecialchars($raw_data->c_pre);
    $text_sel = htmlspecialchars($raw_data->c_sel);
    $text_suf = htmlspecialchars($raw_data->c_suf);
   
    $context = $text_pre . '<span style="color:#f00;">'.$text_sel.'</span>' . $text_suf;
    $url = $insert['url'] . '?error_id=' . $error_id;

    $email = new Email();
    
    $email->send(
        'mistakes@gioc.kiev.ua',
        'Звiт про помилку',
        '',
        'mistakes',
        array(
            'context' => $context,
            'comment' => htmlspecialchars($raw_data->comment),
            'link' => htmlspecialchars($url)
        )
    );
