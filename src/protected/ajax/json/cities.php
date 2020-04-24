<?php

use cri2net\php_pdo_db\PDO_DB;

$list = PDO_DB::table_list(TABLE_PREFIX . 'cities', 'is_active=1', 'pos');
echo json_encode($list, JSON_UNESCAPED_UNICODE);
