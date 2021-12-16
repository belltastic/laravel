<?php

use Belltastic\ApiClient;
use Belltastic\Exceptions\MissingApiKeyException;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    queueMockResponse(200, []);
    config(['belltastic.api_key' => 'valid-key']);
});

it('expects JSON response', function () {
    $client = new ApiClient('test-token');

    $client->get('projects');

    assertRequestCount(1);
    $request = getFirstRequest();
    assertTrue($request->hasHeader('Accept'));
    assertEquals('application/json', $request->getHeaderLine('Accept'));
});

it('sends API token in the header', function () {
    $client = new ApiClient('test-token');

    $client->get('projects');

    assertRequestCount(1);
    $request = getFirstRequest();
    assertTrue($request->hasHeader('Authorization'));
    assertEquals('Bearer test-token', $request->getHeaderLine('Authorization'));
});

it('can add custom headers on requests', function () {
    $client = new ApiClient('test-token', [
        'headers' => [
            'X-Custom-Header' => 'test value',
        ],
    ]);

    $client->get('projects');

    assertRequestCount(1);
    $request = getFirstRequest();
    assertEquals('Bearer test-token', $request->getHeaderLine('Authorization'));
    assertEquals('test value', $request->getHeaderLine('X-Custom-Header'));
});

it('contains the base URI from configuration if not provided', function () {
    config(['belltastic.base_uri' => 'https://test.example.com']);
    $client = new ApiClient();

    $client->get('projects');

    assertRequestCount(1);
    $request = getFirstRequest();
    assertEquals('test.example.com', $request->getUri()->getHost());
    assertEquals('/projects', $request->getUri()->getPath());
    assertEquals('https', $request->getUri()->getScheme());
});

it('contains custom base URI if provided', function () {
    config(['belltastic.base_uri' => 'https://test.example.com']);
    $client = new ApiClient('test-token', [
        'base_uri' => 'http://customwebsite.com/api/v1',
    ]);

    $client->get('projects');

    assertRequestCount(1);
    $request = getFirstRequest();
    assertEquals('customwebsite.com', $request->getUri()->getHost());
    assertEquals('/api/v1/projects', $request->getUri()->getPath());
    assertEquals('http', $request->getUri()->getScheme());
});

it('takes a default API token from configuration if not already present', function () {
    config(['belltastic.api_key' => 'valid-token']);
    $client = new ApiClient();

    $client->get('projects');

    assertRequestCount(1);
    assertEquals('Bearer valid-token', getFirstRequest()->getHeaderLine('Authorization'));
});

it('throws an exception if no api key is present anywhere', function () {
    config(['belltastic.api_key' => '']);
    $client = new ApiClient();

    try {
        $client->get('projects');
    } catch (\Exception $exception) {
        assertInstanceOf(MissingApiKeyException::class, $exception);
        assertEquals('API key not set. Please set the "belltastic.api_key" configuration value, '
            .'or use \Belltastic\Belltastic::setApiKey($key) method before making requests.', $exception->getMessage());
        return;
    }

    $this->fail('MissingApiKeyException was never thrown when the API key was missing.');
});
