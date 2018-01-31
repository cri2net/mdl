<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>
<div class="container">
    <content>
        <div class="portlet">
        	<div class="text" >
			<h1 class="big-title">Державна служба охорони</h1>
			<?php
			    $email = (Authorization::isLogin()) ? $__userData['email'] : '';
			?>
			<iframe src="https://www.gerc.ua/paygate.php?service=dso&owner=kmda&locale=ua&email=<?= $email; ?>" frameborder="0" style="width:820px; height:995px;"></iframe>
			</div>
		</div>
	</content>
</div>