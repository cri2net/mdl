<?php
echo $oschad->get_html_fields();
Oschad::logPaysys('oschad_mycard', 'QUERY', var_export($oschad->get_fields(), true));
