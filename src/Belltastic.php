<?php

namespace Belltastic;

class Belltastic
{
    public static $apiKey;

    public static function setApiKey($apiKey)
    {
        static::$apiKey = $apiKey;
    }

    public static function getApiKey()
    {
        return static::$apiKey;
    }
}
