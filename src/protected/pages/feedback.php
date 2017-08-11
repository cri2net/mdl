<?php
    use cri2net\php_pdo_db\PDO_DB;
?>
<body>

<?php require_once(PROTECTED_DIR . '/layouts/navbar_inner.php'); ?>
<?php require_once(PROTECTED_DIR . '/scripts/breadcrumbs.php'); ?>

<div class="container">
	<content>
		<div class="portlet">
			<h2>Питання до фахівця</h2><br/>
			<?php
				switch($_POST['action']) {
					case 'request-service':
						/* Add request processing */
						require_once(PROTECTED_DIR . '/pages/feedback/result.php');
						break;
					default:
						require_once(PROTECTED_DIR . '/pages/feedback/form.php');
						break;
				}
			?>
		</div>
	</content>
</div>