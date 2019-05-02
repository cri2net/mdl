<?php
class Encryptor
{
    private $private_key;
    private $public_key;

    public function __construct($private_key, $public_key)
    {
        $this->private_key = $private_key;
        $this->public_key = $public_key;
    }

    public function get_sign($string)
    {
        $priv_key_str = file_get_contents($this->private_key);
        $priv_key = openssl_pkey_get_private($priv_key_str);

        openssl_private_encrypt(base64_encode(sha1($string)), $sign, $priv_key);
        return base64_encode($sign);
    }

    public function check_sign($sign_base64, $string)
    {
        $sign = base64_decode($sign_base64);
        $pub_key_str = file_get_contents($this->public_key);
        $pub_key = openssl_pkey_get_public($pub_key_str);
        openssl_public_decrypt($sign, $hash, $pub_key);

        $my_hash = base64_encode(sha1($string));
        return (strcasecmp($hash, $my_hash) == 0);
    }
}
