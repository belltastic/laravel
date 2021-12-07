<?php

use Belltastic\Tests\TestCase;
use GuzzleHttp\Psr7\Request;
use function PHPUnit\Framework\assertNotNull;

uses(TestCase::class)->in(__DIR__);

function mockApiClient()
{
    test()->mockApiClient();
}

function resetApiClient()
{
    test()->resetApiClient();
}

function clearRequests()
{
    test()->clearRequests();
}

function queueMockResponse(int $statusCode, $data = [], $headers = [])
{
    return test()->queueMockResponse($statusCode, $data, $headers);
}

function getFirstRequest(): ?Request
{
    return test()->getFirstRequest();
}

function getLastRequest(): ?Request
{
    return test()->getLastRequest();
}

function getRequests(): array
{
    return test()->getRequests();
}

function loadTestFile(string $path, $replacements = [])
{
    $contents = json_decode(file_get_contents(__DIR__.'/'.$path), true);

    foreach ($replacements as $key => $value) {
        \Illuminate\Support\Arr::set($contents, $key, $value);
    }

    return $contents;
}

function assertRequestCount(int $expectedCount)
{
    test()->assertRequestCount($expectedCount);
}

function assertRequestIs(Request $request = null, string $method = null, string $path = null, array $data = null)
{
    assertNotNull($request);

    if (! is_null($method)) {
        expect($request->getMethod())->toBe(strtoupper($method));
    }

    if (! is_null($path)) {
        expect($request->getUri()->getPath())->toBe($path);
    }

    if (! is_null($data)) {
        expect((string) $request->getBody())->toBe(json_encode($data));
    }
}
