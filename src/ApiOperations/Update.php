<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;

trait Update
{
    public function update($attributes = [], $options = [])
    {
        $this->fill($attributes);

        $client = new ApiClient($options['apiKey'] ?? null, $options);
        $client->put($this->instanceUrl(), $this->toArray(), $options['headers'] ?? []);

        return $this;
    }

    public function save($options = [])
    {
        $client = new ApiClient($options['apiKey'] ?? null, $options);
        $client->put($this->instanceUrl(), $this->toArray(), $options['headers'] ?? []);

        return true;
    }
}
