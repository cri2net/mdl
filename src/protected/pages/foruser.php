<h1 class="big-title">Споживачу</h1>
<div class="foruser">
    <?php
        $list = PDO_DB::table_list(
            TABLE_PREFIX . 'text',
            "variable IN ('FORUSER_CABINET', 'FORUSER_TERMINAL', 'FORUSER_BANKS', 'FORUSER_CALCS', 'FORUSER_LINKS', 'FORUSER_NEWS')"
        );
        for ($i=0; $i < count($list); $i++) { 
            $_list[$list[$i]['variable']] = str_ireplace('{SITE_URL}', BASE_URL, $list[$i]['text']);
        }

        echo $_list['FORUSER_CABINET'],
             $_list['FORUSER_TERMINAL'],
             $_list['FORUSER_BANKS'],
             $_list['FORUSER_CALCS'],
             $_list['FORUSER_LINKS'],
             $_list['FORUSER_NEWS'];
    ?>
</div>
