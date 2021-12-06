<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\ApiResource;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

trait All
{
    abstract public function listUrl();

    /**
     * @param $options
     * @return Collection|LazyCollection
     */
    protected function _all($options = [])
    {
        $client = new ApiClient(
            $options['api_key'] ?? null,
            $options ?? []
        );

        if ($this->paginated) {
            return LazyCollection::make(function () use ($client) {
                list('data' => $data, 'links' => $links, 'meta' => $meta) = $client->get($this->listUrl());
                $data = array_reverse($data);   // prepare for `array_pop` later

                while (! empty($data) || ! empty($links['next'])) {
                    if (empty($data) && $nextPage = $links['next']) {
                        list('data' => $data, 'links' => $links, 'meta' => $meta) = $client->get($nextPage);
                        $data = array_reverse($data);   // prepare for `array_pop` later
                    }

                    $instance = new static();
                    $instance->fill(array_pop($data));
                    yield $instance;
                }
            })->remember();
        } else {
            $data = $client->get($this->listUrl());

            return Collection::make($data)->map(function ($item) {
                /** @var ApiResource $instance */
                $instance = new static();
                $instance->fill($item);

                return $instance;
            });
        }
    }

    public static function all($options = [])
    {
        return (new static())->_all($options);
    }
}
