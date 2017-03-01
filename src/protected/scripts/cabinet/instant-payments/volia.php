<h1 class="big-title">Воля</h1>
<br><br><br>
<?php
    $email = (Authorization::isLogin()) ? $__userData['email'] : '';
?>
<iframe src="https://www.gerc.ua/paygate.php?service=volia&owner=cks&locale=ua&email=<?= $email; ?>" frameborder="0" style="width:820px; height:888px;"></iframe>
