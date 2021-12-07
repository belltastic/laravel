<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;

trait Find
{
    abstract public function instanceUrl(): string;

    /**
     * Re-fetch the model from the API
     *
     * @return $this
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function refresh(): self
    {
        $client = new ApiClient($this->_apiKey, $this->_options ?? []);

        $response = $client->get($this->instanceUrl());

        $this->fill($response);

        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws UnauthorizedException
     * @throws GuzzleException
     * @throws ValidationException
     */
    public static function find($id, $options = []): self
    {
        $instance = new static(['id' => $id], $options);
        $instance->refresh();

        return $instance;
    }
}
