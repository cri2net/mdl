<?php
    if (isset($__route_result['values']['action'])) {
        $post_process_file = $__route_result['values']['action'];
    } else {
        Http::redirect(BASE_URL, false);
    }
    
    $file = ROOT . '/protected/controllers/post/' . $post_process_file . '.php';
    if (file_exists($file)) {
        $redirect_to = require_once($file);
    }

    $redirect_to = ($redirect_to) ? $redirect_to : BASE_URL . "/$post_process_file/";
    Http::redirect($redirect_to, false);
