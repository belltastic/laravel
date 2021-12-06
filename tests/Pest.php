<?php

use Belltastic\Tests\TestCase;
use GuzzleHttp\Psr7\Request;

uses(TestCase::class)->in(__DIR__);

function getFirstRequest(): ?Request
{
    return test()->getFirstRequest();
}

function assertRequestIs(Request $request = null, string $method = null, string $path = null, array $data = null)
{
    \PHPUnit\Framework\assertNotNull($request);

    if (!is_null($method)) {
        expect($request->getMethod())->toBe(strtoupper($method));
    }

    if (!is_null($path)) {
        expect($request->getUri()->getPath())->toBe($path);
    }

    if (!is_null($data)) {
        expect((string) $request->getBody())->toBe(json_encode($data));
    }
}
