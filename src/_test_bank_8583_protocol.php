<?php
require_once(__DIR__ . '/protected/config.php');


if (!in_array(USER_REAL_IP, ['46.151.192.106', '195.138.83.178', '127.0.0.1', '195.138.72.249'])) {
    exit('Access denied');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<?php
    $jak = new JAK8583();

    // die();
    $Khreshchatyk = new Khreshchatyk();

    $iso = $Khreshchatyk->makeTestPayment();
?>
</body>
</html>