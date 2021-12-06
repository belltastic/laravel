<?php

namespace Belltastic\Exceptions;

class ValidationException extends \Exception
{
    /** @var array Validation errors */
    public $errors;

    public function __construct($message = "", array $errors = [])
    {
        parent::__construct($message);
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
