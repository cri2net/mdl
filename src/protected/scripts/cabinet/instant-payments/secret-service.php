<h1 class="big-title">Державна служба охорони</h1>
<br><br><br>
<?php
    $email = (Authorization::isLogin()) ? $__userData['email'] : '';
?>
<iframe src="https://www.gerc.ua/paygate.php?service=dso&owner=cks&locale=ua&email=<?= $email; ?>" frameborder="0" style="width:820px; height:995px;"></iframe>
