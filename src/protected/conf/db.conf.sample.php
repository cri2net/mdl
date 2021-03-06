<?php

switch (USER_REAL_IP) {
    case '127.0.0.1':
    case '::1':
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'mdl');
        define('DB_USER', 'root');
        define('DB_PASSWORD', 'root');
        break;
    
    default:
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'mdl');
        define('DB_USER', 'root');
        define('DB_PASSWORD', 'root');
}

define('API_URL', 'https://ppp.gerc.ua:4445');
define('TABLE_PREFIX', 'mdl_');

define('DB_TBL_CITIES',           TABLE_PREFIX . 'cities');
define('DB_TBL_FLATS',            TABLE_PREFIX . 'flats');
define('DB_TBL_HOUSES',           TABLE_PREFIX . 'houses');
define('DB_TBL_PAYMENT',          TABLE_PREFIX . 'payment');
define('DB_TBL_PAYMENT_SERVICES', TABLE_PREFIX . 'payment_services');
define('DB_TBL_STREETS',          TABLE_PREFIX . 'streets');
define('DB_TBL_USER_FLATS',       TABLE_PREFIX . 'user_flats');
define('DB_TBL_USER_CODES',       TABLE_PREFIX . 'user_codes');
define('DB_TBL_USERS',            TABLE_PREFIX . 'users');
