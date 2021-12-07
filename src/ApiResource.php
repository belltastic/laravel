<?php

namespace Belltastic;

abstract class ApiResource extends BelltasticObject
{
    protected $_apiKey;
    protected $_options;

    protected $paginated = true;

    public function __construct($attributes = [], $options = [])
    {
        $this->fill($attributes);
        $this->_options = $options;

        if (array_key_exists('api_key', $options)) {
            $this->_apiKey = $options['api_key'];
        }
    }

    abstract protected function listUrl(): string;

    abstract protected function instanceUrl(): string;
}
