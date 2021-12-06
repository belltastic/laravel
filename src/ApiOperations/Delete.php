<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Illuminate\Support\Facades\Date;

trait Delete
{
    public function delete($options = [])
    {
        $client = new ApiClient($options['apiKey'] ?? null, $options ?? []);
        $response = $client->delete($this->instanceUrl(), $options['headers'] ?? []);
        $this->fill(['deleted_at' => Date::parse($response['data']['deleted_at'])]);

        return true;
    }

    public function forceDelete($options = [])
    {
        $client = new ApiClient($options['apiKey'] ?? null, $options ?? []);
        $response = $client->delete($this->instanceUrl(), ['force' => true], $options['headers'] ?? []);
        $this->fill(['deleted_at' => Date::parse($response['data']['deleted_at'])]);

        return null;
    }
}
