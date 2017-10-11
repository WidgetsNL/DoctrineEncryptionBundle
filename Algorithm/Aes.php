<?php

namespace WidgetsNL\DoctrineEncryptionBundle\Algorithm;

use WidgetsNL\DoctrineEncryptionBundle\Exception\InvalidAlgorithmKeyException;
use WidgetsNL\DoctrineEncryptionBundle\Exception\InvalidCipherLength;

class Aes implements Algorithm
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $cipher_method;

    /**
     * @var string
     */
    const CIPHER_MODE = 'cbc';

    /**
     * @var integer
     */
    const CIPHER_KEY_SIZE = 256;

    /**
     * @var integer
     */
    const KEY_MIN_SIZE = 5;

    /**
     * Aes256Cbc constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        if ($key == null) {
            throw new InvalidAlgorithmKeyException(__CLASS__ . ' requires a key');
        }
        if (strlen($key) < self::KEY_MIN_SIZE) {
            throw new InvalidAlgorithmKeyException(__CLASS__ . ' requires a key of minimum length ' . self::KEY_MIN_SIZE);
        }
        $this->key           = $key;
        $this->cipher_method = 'aes-' . self::CIPHER_KEY_SIZE . '-' . self::CIPHER_MODE;
    }

    /**
     * @param $payload
     *
     * @return string
     */
    public function encrypt($payload)
    {
        $iv        = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher_method));
        $encrypted = openssl_encrypt($payload, $this->cipher_method, $this->key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * @param $payload
     *
     * @return string
     */
    public function decrypt($payload)
    {
        $cipher_length = openssl_cipher_iv_length($this->cipher_method);
        $iv            = substr(base64_decode($payload), 0, $cipher_length);
        $data          = substr(base64_decode($payload), $cipher_length);
        if (strlen($iv) != $cipher_length) {
            throw new InvalidCipherLength('Cipher length does not validate, not able to decrypt value');
//            @trigger_error('Cipher length does not validate, not able to decrypt value', E_USER_WARNING);
//            return $payload;
        }

        return openssl_decrypt($data, $this->cipher_method, $this->key, 0, $iv);
    }
}
