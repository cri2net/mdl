<?php
	foreach($formArray as $field => $value)
	{
		?>
		<input type="hidden" name="<?= $field; ?>" value="<?= $value; ?>">
		<?php
	}
