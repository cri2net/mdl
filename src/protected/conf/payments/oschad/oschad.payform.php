<?php
echo $oschad->get_html_fields();
log_paysys('oschad', 'QUERY', var_export($oschad->get_fields(), true));
?>
