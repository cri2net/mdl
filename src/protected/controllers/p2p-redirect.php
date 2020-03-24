<?php

    use cri2net\php_pdo_db\PDO_DB;

    if (empty($_GET['id'])) {
        Http::redirect(BASE_URL);
    }

    $payment = PDO_DB::row_by_id(TABLE_PREFIX . 'payment', $_GET['id']);

    if (!$payment || ($payment['type'] != 'p2p') || ($payment['status'] != 'new')) {
        Http::redirect(BASE_URL);
    }

    $payment['processing_data'] = json_decode($payment['processing_data'], true);
    $response = $payment['processing_data']['response'];
    if (empty($response) || empty($response['ascUrl'])) {
        Http::redirect(BASE_URL);
    }

    $response['acsParms'] = json_decode($response['acsParms'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting</title>
</head>
<body onload="document.getElementById('form').submit();">.
    <form id="form" action="<?= $response['ascUrl']; ?>" method="post">
        <?php
            foreach ($response['acsParms'] as $key => $value) {
                ?>
                <input type="hidden" value="<?= htmlspecialchars($value, ENT_QUOTES); ?>" name="<?= $key; ?>" />
                <?php
            }
        ?>
    </form>
</body>
</html>
