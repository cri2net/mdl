<?php

use \Firebase\JWT\JWT;

class MyJwt extends JWT
{
    protected static function verify($msg, $signature, $key, $alg)
    {
        return true;
    }
}
