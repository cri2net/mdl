<?php
    switch ($__route_result['action']) {
        case '403':
            header("HTTP/1.1 403 Forbidden");
            break;
        
        case '404':

            // сначала надо проверить, действительно ли мы не знаем этой страницы
            $static_page = StaticPage::getByURI(null, $__static_pages_array);
            if ($static_page && $static_page['is_active']) {
                $__route_result['controller'] = 'static_page';
                $__route_result['action'] = 'index';
                require_once(ROOT . '/protected/controllers/static_page.php');
                return;
            }

            header("HTTP/1.1 404 Not Found");
            require_once(ROOT . '/protected/layouts/_header.php');
            require_once(ROOT . '/protected/layouts/errors/404.php');
            require_once(ROOT . '/protected/layouts/_footer.php');
            break;
    }
