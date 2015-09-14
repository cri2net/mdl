<?php

    switch ($_POST['action']) {
        case 'load_more':
            $news_on_page = abs(intval($_POST['news_on_page']));
            $news_current_page = abs(intval($_POST['news_current_page']));
            
            if (($news_on_page == 0) || ($news_current_page == 0)) {
                break;
            }

            $from = ($news_current_page * $news_on_page) - $news_on_page;
            $news = PDO_DB::table_list(News::TABLE, "`is_actual`=1", "created_at DESC", "$from, $news_on_page");

            echo json_encode(['status' => true, 'html' => News::fetchList($news)]);
            break;
    }
