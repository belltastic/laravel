<?php

namespace Belltastic;

class Belltastic
{
    static $apiKey;

    public static function setApiKey($apiKey)
    {
        static::$apiKey = $apiKey;
    }

    public static function getApiKey()
    {
        return static::$apiKey;
    }
}
