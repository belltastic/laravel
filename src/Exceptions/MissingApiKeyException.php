<?php

namespace Belltastic\Exceptions;

class MissingApiKeyException extends Exception
{
    public const DEFAULT_MESSAGE = 'API key not set. Please set the "belltastic.api_key" configuration value, '
            .'or use \Belltastic\Belltastic::setApiKey($key) method before making requests.';

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: self::DEFAULT_MESSAGE, $code, $previous);
    }
}
