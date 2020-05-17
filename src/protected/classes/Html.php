<?php

class Html
{
    /**
     * minify HTML if debug not enabled
     * @param  string $html HTML
     * @return string HTML
     */
    public static function clear($html)
    {
        return self::removeSpaces(self::removeComments($html));
    }

    /**
     * Remove spaces from HTML
     * @param  string $html HTML
     * @return string HTML without spaces
     */
    public static function removeSpaces($html)
    {
        return trim(preg_replace('/>\s+</', '><', $html));
    }

    /**
     * Remove comments from HTML
     * @param  string $html HTML
     * @return string HTML without comments
     */
    public static function removeComments($html)
    {
        return preg_replace('/<!--(.|\s)*?-->/', '', $html);
    }
}
