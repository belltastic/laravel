<?php

namespace Belltastic\Exceptions;

use Psr\Http\Message\RequestInterface;

class ValidationException extends Exception
{
    /** @var array Validation errors */
    public $errors;

    public function __construct($message = "", array $errors = [], RequestInterface $request = null)
    {
        parent::__construct($message, $request);

        $this->errors = $errors;
    }

    public function getErrors($name = null): array
    {
        if (! is_null($name)) {
            return $this->errors[$name] ?? [];
        }

        return $this->errors;
    }

    public function hasError($name): bool
    {
        return array_key_exists($name, $this->errors);
    }

    public function getFirstError($name): ?string
    {
        return $this->getErrors($name)[0] ?? null;
    }
}
