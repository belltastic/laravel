<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\ApiResource;
use Illuminate\Support\Collection;

trait All
{
    abstract public function listUrl();

    protected function _all($options = [])
    {
        $client = new ApiClient(
            $options['apiKey'] ?? null,
            $options ?? []
        );

        if ($this->paginated) {
            list('data' => $data, 'links' => $links, 'meta' => $meta) = $client->get($this->listUrl());
        } else {
            $data = $client->get($this->listUrl());
        }

        return Collection::make($data)->map(function ($item) {
            /** @var ApiResource $instance */
            $instance = new static();
            $instance->fill($item);

            return $instance;
        });
    }

    public static function all($options = [])
    {
        return (new static())->_all($options);
    }
}
