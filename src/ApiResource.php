<?php

namespace Belltastic;

abstract class ApiResource extends BelltasticObject
{
    private $_apiKey;
    private $_options;

    protected $paginated = true;

    public function __construct($attributes = [], $options = [])
    {
        $this->fill($attributes);
        $this->_options = $options;

        if (array_key_exists('apiKey', $options)) {
            $this->_apiKey = $options['apiKey'];
        }
    }

    abstract public function listUrl(): string;

    abstract public function instanceUrl(): string;
}
