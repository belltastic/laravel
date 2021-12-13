<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;

trait Archive
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
    public function archive(array $options = [])
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);
        $response = $client->put($this->instanceUrl().'/archive', $options['headers'] ?? []);
        $this->forceFill($response['data']);

        return $this;
    }
}
