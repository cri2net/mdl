<?php

use cri2net\php_pdo_db\PDO_DB;

$_id = $__route_result['values']['photo_id'];
$_type = $__route_result['values']['type'];
$_size = $__route_result['values']['size'];
$folder = $_size;

switch ($_type) {

    case 'news':
        $img = PDO_DB::row_by_id(TABLE_PREFIX . "{$_type}_images", $_id);
        $good_size = in_array($_size, ['50x50', '100x100fc', '795x266', '410x274', '795x266fc', '387x266fc', '1101x620fc']);
        $img_path = ROOT . "/db_pic/$_type/$folder/{$img['filename']}.jpg";
        $root_image = ROOT . "/db_pic/$_type/original/{$img['filename']}.jpg";
        break;
}

if (!$img || !$good_size) {
    $nophoto = ROOT . '/images/nophoto/' . $folder . '.png';
    if (file_exists($nophoto)) {
        $content = file_get_contents($nophoto);

        header('Content-type: image/png; charset: UTF-8');
        header('cache-control: must-revalidate');
        header("expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT");
        header('Content-Length: ' . strlen($content));

        echo $content;
    }
    return;
}

if (!file_exists($img_path)) {
    $mainImage = imagecreatefromjpeg($root_image);
    $need_size = explode('x', $_size);

    if (stristr($_size, 'fc')) {
        $newImg = lavaImageProcessor::resize($mainImage, $need_size[0], $need_size[1], true, true);
    } else {
        $newImg = lavaImageProcessor::resize($mainImage, $need_size[0], $need_size[1]);
    }
    imageinterlace($newImg, 1);

    if (!file_exists(dirname($img_path))) {
        mkdir(dirname($img_path), 0755, true);
    }

    imagejpeg($newImg, $img_path, 100);
}

$content = file_get_contents($img_path);

header('Content-type: image/jpeg; charset: UTF-8');
header('cache-control: must-revalidate');
header("expires: " . gmdate("D, d M Y H:i:s", time() + 1209600) . " GMT");
header('Content-Length: ' . strlen($content));

echo $content;
