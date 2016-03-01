<?php
$s = $oschad->get_html_fields();
echo $s;

log_paysys('oschad', 'QUERY', $s);
?>
