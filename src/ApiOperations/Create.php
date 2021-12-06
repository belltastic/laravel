<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;

trait Create
{
    public static function create($attributes = [], $options = [])
    {
        $client = new ApiClient($options['apiKey'] ?? null, $options);

        $response = $client->post((new static())->listUrl(), $attributes, $options['headers'] ?? []);

        return new static($response);
    }
}
