<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>
<div class="container">
    <content>
        <div class="portlet">
		<center>	
		<iframe src="https://www.gerc.ua/mobile.gerc.php?owner=cks" frameborder="0" style="width:820px; height:995px;"></iframe>
		</center>
		</div>
	</content>
</div>
<script>
    $(document).keydown(function(e) {
        if ((e.keyCode == 116) || (e.keyCode == 82 && e.ctrlKey)) {
            return false;
        }
    });
</script>
