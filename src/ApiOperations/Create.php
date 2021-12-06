<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;

trait Create
{
    public function _create($attributes = [], $options = [])
    {
        $client = new ApiClient($options['api_key'] ?? null, $options ?? []);

        $response = $client->post($this->listUrl(), $attributes, $options['headers'] ?? []);

        return new static($response);
    }

    public static function create($attributes = [], $options = [])
    {
        return (new static)->_create($attributes, $options);
    }
}
