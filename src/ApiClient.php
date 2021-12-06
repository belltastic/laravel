<?php

namespace Belltastic;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class ApiClient
{
    const USER_AGENT = 'Belltastic-PHP-Laravel/1.0';

    /** @var string|null */
    private $_apiKey;

    /** @var object */
    private $_options;

    /** @var Client */
    private $_client;

    public function __construct($apiKey, $options = [])
    {
        $this->_apiKey = $apiKey;
        if (!$this->_apiKey) {
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, string $path, array $data = [], array $headers = [])
    {
        $client = $this->getClient();

        try {
            $response = $client->request($method, $path, [
                'base_uri' => Str::finish($this->_options->base_uri, '/'),
                'json' => $data,
                'headers' => array_merge([
                    'User-Agent' => ApiClient::USER_AGENT,
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->_apiKey,
                ], $headers),
            ]);
        } catch (\Exception $exception) {
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

    public function delete($path, $headers = [])
    {
        return $this->request('DELETE', $path, [], $headers);
    }
}
