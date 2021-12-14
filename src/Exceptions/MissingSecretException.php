<?php

namespace Belltastic\Exceptions;

class MissingSecretException extends Exception
{
    const DEFAULT_MESSAGE = 'Please provide the Project Secret in order to calculate HMAC value.';

    public function __construct($message = null, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: self::DEFAULT_MESSAGE, $code, $previous);
    }
}
