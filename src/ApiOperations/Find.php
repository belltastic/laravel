<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;

trait Find
{
    abstract public function instanceUrl();

    /**
     * Re-fetch the model from the API
     *
     * @return $this
     */
    public function refresh()
    {
        $client = new ApiClient($this->_apiKey, $this->_options ?? []);

        $response = $client->get($this->instanceUrl());

        $this->fill($response);

        return $this;
    }

    public static function find($id, $options = [])
    {
        $instance = new static(['id' => $id], $options);
        $instance->refresh();

        return $instance;
    }
}
