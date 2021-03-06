<?php

use Belltastic\ApiClient;
use Belltastic\Exceptions\ForbiddenException;
use Belltastic\Exceptions\NotFoundException;
use Belltastic\Exceptions\UnauthorizedException;
use Belltastic\Exceptions\ValidationException;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    config(['belltastic.api_key' => 'valid-key']);
});

it('throws Unauthorized exception if not authenticated properly', function () {
    queueMockResponse(401, ['message' => 'Unauthenticated. With a custom message.']);
    $client = new ApiClient();

    try {
        $client->request('get', 'projects');
        $this->fail('UnauthorizedException was not thrown.');
    } catch (\Exception $exception) {
        expect($exception)->toBeInstanceOf(UnauthorizedException::class);
        expect($exception->getMessage())->toContain('Unauthenticated. With a custom message.');
    }
});

it('throws a NotFound exception if a model was not found', function () {
    queueMockResponse(404, ['message' => 'Model not found.']);
    $client = new ApiClient();

    try {
        $client->request('get', 'project/123');
        $this->fail('NotFound exception was not thrown.');
    } catch (\Exception $exception) {
        expect($exception)->toBeInstanceOf(NotFoundException::class);
        expect($exception->getMessage())->toContain('Model not found.');
    }
});

it('throws a Forbidden exception if the user does not have access to the resource', function () {
    queueMockResponse(403, ['message' => 'Forbidden. You do not have the required permissions.']);
    $client = new ApiClient();

    try {
        $client->request('get', 'project/123');
        $this->fail('ForbiddenException exception was not thrown.');
    } catch (\Exception $exception) {
        expect($exception)->toBeInstanceOf(ForbiddenException::class);
        expect($exception->getMessage())->toContain('Forbidden. You do not have the required permissions.');
    }
});

it('throws a ValidationException if the request does not pass validation', function () {
    $errors = [
        'name' => [
            'The name field is required.',
        ],
        'email' => [
            'The email must be a valid email address.',
        ],
        'avatar_url' => [
            'The avatar url must be a string.',
        ],
    ];
    queueMockResponse(422, [
        'message' => 'The given data was invalid.',
        'errors' => $errors,
    ]);
    $client = new ApiClient();

    try {
        $client->request('get', 'project/123');
        $this->fail('ValidationException exception was not thrown.');
    } catch (\Exception $exception) {
        expect($exception)->toBeInstanceOf(ValidationException::class);
        expect($exception->getMessage())->toContain('The given data was invalid.');

        // working with errors from the exception
        assertCount(3, $exception->getErrors());
        assertEquals($errors, $exception->getErrors());
        assertEquals(['The email must be a valid email address.'], $exception->getErrors('email'));
        assertTrue($exception->hasError('name'));
        assertFalse($exception->hasError('false_field'));
        assertEquals('The name field is required.', $exception->getFirstError('name'));
        assertNull($exception->getFirstError('false_field'));
    }
});
