<?php
class lavaImageProcessor
{
    public static function resize($img, $w, $h, $fill=false, $cut = false)
    {
        $img_w = imagesx($img);
        $img_h = imagesy($img);
        if ($w >= $img_w && $h >= $img_h) {
            return $img;
        }
            
        if ($fill) {
            $W = $w;
            $H = (int)(($W * $img_h) / $img_w);
            if ($H < $h) {
                $H = $h;
                $W = (int)(($H * $img_w) / $img_h);
            }
        } else {
            $W = $w;
            $H = (int)(($W * $img_h) / $img_w);
            if ($H > $h) {
                $H = $h;
                $W = (int)(($H * $img_w) / $img_h);
            }
        }

        if ($cut) {
            $res = imagecreatetruecolor($w, $h);
            imagecopyresampled($res, $img, ($w - $W) >> 1, ($h - $H) >> 1, 0, 0, $W, $H, $img_w, $img_h);
            return $res;
        }

        $res = imagecreatetruecolor($W, $H);
        imagecopyresampled($res, $img, 0, 0, 0, 0, $W, $H, $img_w, $img_h);
        return $res;
    }
}
