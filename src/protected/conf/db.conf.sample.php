<?php
switch (USER_REAL_IP) {
    case '127.0.0.111':
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'cks');
        define('DB_USER', 'root');
        define('DB_PASSWORD', 'root');

        define('API_URL', 'https://bank.gioc.kiev.ua');
        break;
    
    default:
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'gioc_site');
        define('DB_USER', 'giocwww');
        define('DB_PASSWORD', 'skjdgiougKPs8d69t3bhLJGFIOugtlsd');
        
        define('API_URL', 'http://10.12.2.201:8888');
}

define('TABLE_PREFIX', 'cks_');

define('DB_TBL_CITIES',           TABLE_PREFIX . 'cities');
define('DB_TBL_EMAIL_CRON',       TABLE_PREFIX . 'email_cron');
define('DB_TBL_FLATS',            TABLE_PREFIX . 'flats');
define('DB_TBL_HOUSES',           TABLE_PREFIX . 'houses');
define('DB_TBL_PAYMENT',          TABLE_PREFIX . 'payment');
define('DB_TBL_PAYMENT_SERVICES', TABLE_PREFIX . 'payment_services');
define('DB_TBL_STREETS',          TABLE_PREFIX . 'streets');
define('DB_TBL_USER_FLATS',       TABLE_PREFIX . 'user_flats');
define('DB_TBL_USER_CODES',       TABLE_PREFIX . 'user_codes');
define('DB_TBL_USERS',            TABLE_PREFIX . 'users');
