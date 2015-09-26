# README #

### SET UP ###

1. Сперва нужно установить зависимости из файла src/composer.json
    Сделать это можно командами:

        cd src/
        composer install

1. Второй шаг - настройка локального подключения к базе данных.
    Нужно создать файл src/protected/conf/db.conf.local.php с таким содержимым:
    
        <?php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'gioc_site');
        define('DB_USER', 'root');
        define('DB_PASSWORD', 'root');
        
        define('API_URL', 'https://193.200.205.201');
        

1. И, наконец, нужно выполнить команду gulp из корня репозитория
