<?php
    $fontSize     = 10;   // GD1 in px ; GD2 in point
    $marge        = 10;   // between barcode and hri in pixel
    $x            = 90;   // barcode center
    $y            = 20;   // barcode center
    $height       = 39;   // barcode height in 1D ; module size in 2D
    $width        = 1;    // barcode height in 1D ; not use in 2D
    $angle        = 0;    // rotation in degrees : nb : non horizontable barcode might not be usable because of pixelisation
    $image_width  = 180;
    $image_height = 50;

    $code = (string)$_GET['code'];
    $type = 'code128';
    
    $code .= '=';
    

    $im     = imagecreatetruecolor($image_width, $image_height);
    $black  = ImageColorAllocate($im, 0x00, 0x00, 0x00);
    $white  = ImageColorAllocate($im, 0xff, 0xff, 0xff);
    imagefilledrectangle($im, 0, 0, 300, 300, $white);
    

    $data = JBDemonte\Barcode::gd($im, $black, $x, $y, $angle, $type, ['code' => $code], $width, $height);

    
    header('Content-type: image/gif');
    imagegif($im);
    imagedestroy($im);
