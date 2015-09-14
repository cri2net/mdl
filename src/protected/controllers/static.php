<?php
    $ext = pathinfo(basename($__route_result['values']['path']), PATHINFO_EXTENSION);

    switch ($ext) {
        case 'css':
            $filename = ROOT . '/style/' . $__route_result['values']['path'];
            if (file_exists($filename)) {
                header("Content-Type: text/css; charset=utf-8");
                echo file_get_contents($filename);
                exit();
            } else {
                header("HTTP/1.1 404 Not Found");
                exit();
            }
            break;

        case 'js':
            $filename = ROOT . '/js/' . $__route_result['values']['path'];
            if (file_exists($filename)) {
                header("Content-Type: application/javascript; charset=utf-8");
                echo file_get_contents($filename);
                exit();
            } else {
                header("HTTP/1.1 404 Not Found");
                exit();
            }
            break;
    }
