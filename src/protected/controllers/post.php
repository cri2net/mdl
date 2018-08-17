<?php
$uri = substr($route_path, 5); // Отрезаем /post
$uri = str_replace('.', '', $uri); // очень недостаточная мера безопасности
$uri = trim($uri, '/'); // Обрезаем слеши в конце и начале

$file = PROTECTED_DIR . '/controllers/post/' . $uri . '.php';
if (file_exists($file)) {
    $redirect_to = require_once($file);
}

$redirect_to = ($redirect_to && is_string($redirect_to)) ? $redirect_to : BASE_URL . "/$uri/";
Http::redirect($redirect_to);
