<?php

namespace Belltastic;

use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class ApiClient
{
    public const USER_AGENT = 'Belltastic-PHP-Laravel/1.0';

    /** @var string|null */
    private $_apiKey;

    /** @var object */
    private $_options;

    /** @var Client */
    private $_client;

    public function __construct(string $apiKey = null, array $options = [])
    {
        $this->_apiKey = $apiKey;
        if (! $this->_apiKey) {
            $this->_apiKey = Belltastic::getApiKey();
        }

        $this->setOptions($options);
    }

    public function getClient()
    {
        if (is_null($this->_client)) {
            $this->_client = app('belltastic-api-client');
        }

        return $this->_client;
    }

    public function setOptions($options = [])
    {
        $config = config('belltastic');

        $this->_options = (object) array_merge([
            'base_uri' => $config['base_uri'],
        ], $options ?? []);

        return $this;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $data
     * @param array $headers
     * @return mixed
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function request(string $method, string $path, array $data = [], array $headers = [])
    {
        $client = $this->getClient();

        try {
            $response = $client->request($method, $path, [
                'base_uri' => Str::finish($this->_options->base_uri, '/'),
                'json' => $data,
                'headers' => array_merge([
                    'User-Agent' => $this->_options->user_agent ?? ApiClient::USER_AGENT,
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->_apiKey,
                ], $this->_options->headers ?? [], $headers),
            ]);
        } catch (ClientException $exception) {
            if ($response = $exception->getResponse()) {
                $body = json_decode((string) $response->getBody(), true);

                if ($response->getStatusCode() === 401) {
                    throw new UnauthorizedException($body['message'] ?? $exception->getMessage());
                } elseif ($response->getStatusCode() === 403) {
                    throw new ForbiddenException($body['message'] ?? $exception->getMessage());
                } elseif ($response->getStatusCode() === 404) {
                    throw new NotFoundException($body['message'] ?? $exception->getMessage());
                } elseif ($response->getStatusCode() === 422) {
                    throw new ValidationException(
                        $body['message'] ?? $exception->getMessage(),
                        $body['errors'] ?? []
                    );
                }
            }

            throw $exception;
        }

        return json_decode((string) $response->getBody(), true);
    }

    public function get($path, $headers = [])
    {
        return $this->request('GET', $path, [], $headers);
    }

    public function post($path, $data = [], $headers = [])
    {
        return $this->request('POST', $path, $data, $headers);
    }

    public function put($path, $data = [], $headers = [])
    {
        return $this->request('PUT', $path, $data, $headers);
    }

    public function delete($path, $data = [], $headers = [])
    {
        return $this->request('DELETE', $path, $data, $headers);
    }
}
