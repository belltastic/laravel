<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;

trait Create
{
    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws GuzzleException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    protected function _create($attributes = [], $options = []): self
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);

        $response = $client->post($this->listUrl(), $attributes, $options['headers'] ?? []);

        return new static($response);
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws UnauthorizedException
     * @throws GuzzleException
     * @throws ValidationException
     */
    public static function create($attributes = [], $options = []): self
    {
        return (new static())->_create($attributes, $options);
    }
}
