<?php
    switch (USER_REAL_IP) {
        case '127.0.0.1':
            define('DB_HOST', 'localhost');
            define('DB_NAME', 'gioc_site');
            define('DB_USER', 'root');
            define('DB_PASSWORD', '');

            // define('API_URL', 'http://193.200.205.201:8888');
            define('API_URL', 'https://193.200.205.201');
            break;
        
        default:
            if (strcasecmp($_SERVER['HTTP_HOST'], 's1.mailing.com.ua') == 0) {
                define('DB_HOST', 'localhost');
                define('DB_NAME', 'gioc_site');
                define('DB_USER', 'gioc_site');
                define('DB_PASSWORD', 'XcKeWfZtHJWFYUKs');

                define('API_URL', 'https://193.200.205.201');
            } else {
                define('DB_HOST', 'localhost');
                define('DB_NAME', 'gioc_site');
                define('DB_USER', 'root');
                define('DB_PASSWORD', 'kykyev');
                
                define('API_URL', 'http://10.12.2.201:8888');
            }
    }
    
    define('TABLE_PREFIX', 'gioc_');

    define('DB_TBL_CITIES', 'gioc_cities');
    define('DB_TBL_FLATS', 'gioc_flat');
    define('DB_TBL_HOUSES', 'gioc_houses');
    define('DB_TBL_NEWS', 'gioc_news');
    define('DB_TBL_NEWS_IMAGES', 'gioc_news_images');
    define('DB_TBL_PAGE_VIEWS', 'gioc_page_views');
    define('DB_TBL_PAGES', 'gioc_pages');
    define('DB_TBL_PAGES_LINKS', 'gioc_pages_links');
    define('DB_TBL_PAYMENT', 'gioc_payment');
    define('DB_TBL_PAYMENT_SERVICES', 'gioc_payment_services');
    define('DB_TBL_STREETS', 'gioc_streets');
    define('DB_TBL_USER_FLATS', 'gioc_user_flats');
    define('DB_TBL_USERS', 'gioc_users');
    define('DB_TBL_MENUS', 'gioc_menus');
