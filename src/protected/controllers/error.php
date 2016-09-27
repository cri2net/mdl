<?php
switch ($__route_result['action']) {
    case '403':
        header("HTTP/1.1 403 Forbidden");
        break;
    
    case '404':

        header("HTTP/1.1 404 Not Found");
        require_once(PROTECTED_DIR . '/layouts/_header.php');
        require_once(PROTECTED_DIR . '/layouts/errors/404.php');
        require_once(PROTECTED_DIR . '/layouts/_footer.php');
        break;
}
