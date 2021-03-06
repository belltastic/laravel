<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;

trait Update
{
    abstract protected function instanceUrl(): string;

    /**
     * Update the model with new values.
     * @param array $attributes
     * @param array $options
     * @return $this
     *
     * @throws ValidationException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws GuzzleException
     */
    public function update(array $attributes = [], array $options = []): self
    {
        $this->fill($attributes);

        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);
        $response = $client->put($this->instanceUrl(), $this->toRequestArray(), $options['headers'] ?? []);
        $this->forceFill($response['data']);

        return $this;
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws UnauthorizedException
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function save($options = [])
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);
        $response = $client->put($this->instanceUrl(), $this->toRequestArray(), $options['headers'] ?? []);
        $this->forceFill($response['data']);

        return $this;
    }
}
