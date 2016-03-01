<?php
$s = $oschad->get_html_fields(true);
echo $s;

log_paysys('oschad', 'QUERY', $s);  
?>
