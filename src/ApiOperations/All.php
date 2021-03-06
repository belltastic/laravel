<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

trait All
{
    abstract protected function listUrl(): string;

    /**
     * @param array $options
     * @return Collection|LazyCollection
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws GuzzleException
     */
    protected function _all(array $options = [])
    {
        $client = new ApiClient(
            $options['api_key'] ?? $this->_apiKey,
            $options ?? []
        );

        if ($this->paginated) {
            return LazyCollection::make(function () use ($client) {
                list('data' => $data, 'links' => $links) = $client->get($this->listUrl());
                $data = array_reverse($data);   // prepare for `array_pop` later

                while (! empty($data) || ! empty($links['next'])) {
                    if (empty($data) && $nextPage = $links['next']) {
                        list('data' => $data, 'links' => $links) = $client->get($nextPage);
                        $data = array_reverse($data);   // prepare for `array_pop` later
                    }

                    yield new static(array_pop($data));
                }
            })->remember();
        } else {
            $data = $client->get($this->listUrl());

            return Collection::make($data)->map(function ($item) {
                return new static($item);
            });
        }
    }

    /**
     * @param array $options
     * @return Collection|LazyCollection
     * @throws ForbiddenException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public static function all(array $options = [])
    {
        return (new static())->_all($options);
    }
}
