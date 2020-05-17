<?php

class UserAgent
{
    /**
     * Detect User-Agent and user IP
     * @return StdClass
     */
    public static function detect()
    {
        static $me = null;
        if ($me !== null) {
            return $me;
        }

        $me = new StdClass();

        $me->ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER["REMOTE_ADDR"] : '';
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $me->ip = (isset($_SERVER["HTTP_X_REAL_IP"])) ? $_SERVER["HTTP_X_REAL_IP"] : $_SERVER["HTTP_X_FORWARDED_FOR"];
        }

        $me->user_agent = (isset($_SERVER['HTTP_USER_AGENT']))
            ? $_SERVER['HTTP_USER_AGENT']
            : ((isset($GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'])) ? $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'] : '');

        $me->is_bot = (bool)preg_match('/robot|spider|crawler|bot|curl|^$/i', $me->user_agent);

        $me->is_local = (empty($me->ip))
            ? false
            : !filter_var($me->ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

        return $me;
    }
}
