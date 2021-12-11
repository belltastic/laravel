<?php

namespace Belltastic\ApiOperations;

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Exception\GuzzleException;

trait Destroy
{
    abstract protected function instanceUrl(): string;

    /**
     * @param array $options
     * @return void
     * @throws ForbiddenException
     * @throws GuzzleException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function destroy(array $options = [])
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options ?? []);
        $client->delete($this->instanceUrl(), [], $options['headers'] ?? []);
        $this->deleted_at = now()->micro(0);
    }
}
