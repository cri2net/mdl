<?php
    require_once(ROOT . '/protected/layouts/_header.php');
        
    $file = ROOT . '/protected/pages/' . $__route_result['action'] . '.php';
    
    if (file_exists($file)) {
        require_once($file);
        $page_id_to_log = $__route_result['action'];
    } elseif (isset($__route_result['values']['subpage'])) {
        $subpage = basename($__route_result['values']['subpage']);
        $file = ROOT . "/protected/pages/{$__route_result['action']}/$subpage.php";
        
        if (file_exists($file)) {
            require_once($file);
            $page_id_to_log = "{$__route_result['action']}/$subpage";
        }
    } else {
        $file = ROOT . "/protected/pages/{$__route_result['action']}/index.php";
        
        if (file_exists($file)) {
            require_once($file);
            $page_id_to_log = "{$__route_result['action']}/index";
        }
    }
    
    switch ($__route_result['action']) {
        case 'news-item':
            StaticPage::logView($__news_item['id'], 'news');
            News::incrementViews($__news_item['id']);
            break;
        
        default:
            StaticPage::logView($page_id_to_log, 'other');
    }

    require_once(ROOT . '/protected/layouts/_footer.php');
