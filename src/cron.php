<?php
    require_once(__DIR__ . '/protected/config.php');
    $arr = scandir(ROOT . '/protected/classes');

    for ($i=0; $i < count($arr); $i++) {
        $filename = preg_replace('/\\\\/', '/', $arr[$i]);
        if (preg_match('/([a-z0-9_\-]+)\.php$/i', $filename, $matches)) {
            if (isset($matches[1])) {
                try {
                    $rm = new ReflectionMethod($matches[1], 'cron');
                    $rm->invoke(null);
                } catch (Exception $e) {
                }
            }
        }
    }
