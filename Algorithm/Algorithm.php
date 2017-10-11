<?php

namespace WidgetsNL\DoctrineEncryptionBundle\Algorithm;

interface Algorithm
{
    /**
     * Constructor
     *
     * @param $key
     */
    public function __construct($key);

    /**
     * @param $payload
     *
     * @return mixed
     */
    public function encrypt($payload);

    /**
     * @param $payload
     *
     * @return mixed
     */
    public function decrypt($payload);
}
