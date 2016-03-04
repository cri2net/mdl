<?php
echo $oschad->get_html_fields();
Oschad::logPaysys('oschad', 'QUERY', var_export($oschad->get_fields(), true));
