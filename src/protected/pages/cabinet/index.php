<?php

use cri2net\php_pdo_db\PDO_DB;

$list = PDO_DB::table_list(TABLE_PREFIX . 'text', "variable IN ('CABINET_BLOCK_1', 'CABINET_BLOCK_2', 'CABINET_BLOCK_3')");
for ($i=0; $i < count($list); $i++) {
    $_list[$list[$i]['variable']] = str_replace('{SITE_URL}', BASE_URL, $list[$i]['text']);
}

echo $_list['CABINET_BLOCK_1'], $_list['CABINET_BLOCK_2'], $_list['CABINET_BLOCK_3'];
