<?php
require_once(__DIR__ . '/protected/config.php');


if (!in_array(USER_REAL_IP, ['46.151.192.106', '195.138.83.178', '127.0.0.1'])) {
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
    $Khreshchatyk = new Khreshchatyk();
    $iso = $Khreshchatyk->checkBalance();

    
    //get parsing result
    print 'ISO: '. $iso. "<br>\r\n";
    print 'MTI: '. $jak->getMTI(). "<br>\r\n";
    print 'ISO: '. $jak->getISO(). "<br>\r\n";
    print 'Bitmap: '. $jak->getBitmap(). "<br>\r\n";
    print 'Data Element: '; var_dump($jak->getData());
?>
</body>
</html>