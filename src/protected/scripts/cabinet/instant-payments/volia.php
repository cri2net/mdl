<?php
    $email = (Authorization::isLogin()) ? $__userData['email'] : '';
?>
<body>
<?php
    require_once(PROTECTED_DIR . '/layouts/navbar_inner.php');
    require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php');
?>
<div class="container">
    <content>
        <div class="portlet">
		<center>
		<iframe src="https://www.gerc.ua/paygate.php?service=volia&owner=cks&locale=ua&email=<?= $email; ?>" frameborder="0" style="width:820px; height:888px;"></iframe></center>
		</div>
	</content>
</div>