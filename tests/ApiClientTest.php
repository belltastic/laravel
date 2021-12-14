<?php

use Belltastic\ApiClient;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    queueMockResponse(200, []);
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
