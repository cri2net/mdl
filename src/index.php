<?php
    require_once(__DIR__ . '/protected/config.php');
    
    $router = new Routing(ROOT . '/protected/conf/routing.xml', true);
    $route_path = (strpos($_SERVER['REQUEST_URI'], '?') !== false)
        ? substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
        : $_SERVER['REQUEST_URI'];
    $__route_result = $router->get($route_path);

    $file = __DIR__ . '/protected/controllers/' . $__route_result['controller'] . '.php';
    if (file_exists($file)) {
        require_once($file);
    }
