<?php
    $url = "http://www.gerc.ua/cks/invoice/?uid=4&f=53292&hash2=b6b28a6f8f6c27fe17909b4ac44c43fa&email_mode=1";
    $content = Http::HttpGet($url, false, false);

    echo $content;