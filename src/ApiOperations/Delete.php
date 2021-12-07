<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;

trait Delete
{
    abstract protected function instanceUrl(): string;

    /**
     * @param array $options
     * @return void
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function delete(array $options = [])
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);
        $response = $client->delete($this->instanceUrl(), $options['headers'] ?? []);
        $this->fill(['deleted_at' => $response['data']['deleted_at']]);
    }

    /**
     * @param array $options
     * @return void
     * @throws ForbiddenException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function forceDelete(array $options = [])
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);
        $response = $client->delete($this->instanceUrl(), ['force' => true], $options['headers'] ?? []);
        $this->fill(['deleted_at' => $response['data']['deleted_at']]);
    }
}
