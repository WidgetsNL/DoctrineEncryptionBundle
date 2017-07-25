<?php

namespace WidgetsNL\DoctrineEncryptionBundle\Algorithm;

class Aes256Cbc
{
    private $key;
    const CIPHER_METHOD = 'aes-256-cbc';

    /**
     * Aes256Cbc constructor.
     *
     * @param $salt
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt($payload)
    {
        $iv        = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
        $encrypted = openssl_encrypt($payload, self::CIPHER_METHOD, $this->key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    public function decrypt($payload)
    {
        $cipher_length = openssl_cipher_iv_length(self::CIPHER_METHOD);
        $iv            = substr(base64_decode($payload), 0, $cipher_length);
        $data          = substr(base64_decode($payload), $cipher_length);
        if( strlen($iv) != $cipher_length ) {
            @trigger_error('Cipher length does not validate, not able to decrypt value', E_USER_WARNING);
            return $payload;
        }

        return openssl_decrypt($data, self::CIPHER_METHOD, $this->key, 0, $iv);
    }
}