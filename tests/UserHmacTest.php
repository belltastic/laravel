<?php

use Belltastic\Exceptions\MissingSecretException;
use Belltastic\User;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

beforeEach(function () {
    $this->user = new User([
        'id' => 1234,
        'project_id' => 4321,
    ]);
});

it('can retrieve the user\'s HMAC value', function () {
    // HMAC depends on the project ID, user ID and the project's secret value.
    $secret = 'valid-secret';
    config(['belltastic.projects.'.$this->user->project_id.'.secret' => $secret]);
    $expectedHmac = base64_encode(hash_hmac('sha256', 4321 . ':' . 1234, $secret, true));

    $actualHmac = $this->user->hmac();

    assertTrue(
        hash_equals($expectedHmac, $actualHmac),
        "The returned '$actualHmac' HMAC value does not match the expected '$expectedHmac' value."
    );
});

it('can provide a custom secret value if needed', function () {
    $secret = 'valid-secret';
    config(['belltastic.projects.'.$this->user->project_id.'.secret' => 'total-different-secret']);
    $expectedHmac = base64_encode(hash_hmac('sha256', 4321 . ':' . 1234, $secret, true));

    // first, it's gonna fail because it'll pull the incorrect secret from the config.
    $incorrectHmac = $this->user->hmac();
    assertFalse(
        hash_equals($expectedHmac, $incorrectHmac),
        "The returned '$incorrectHmac' HMAC value match the incorrect '$expectedHmac' value."
    );

    // Now, let's provide the correct secret and it should pass
    $correctHmac = $this->user->hmac($secret);
    assertTrue(
        hash_equals($expectedHmac, $correctHmac),
        "The returned '$correctHmac' HMAC value does not match the expected '$expectedHmac' value."
    );
});

it('can return HMAC from a static method without needing a user instance', function () {
    $secret = 'valid-secret';
    config(['belltastic.projects.'.$this->user->project_id.'.secret' => $secret]);
    $expectedHmac = base64_encode(hash_hmac('sha256', 4321 . ':' . 1234, $secret, true));

    /** @noinspection */
    $actualHmac = User::hmac($this->user->project_id, $this->user->id);

    assertTrue(
        hash_equals($expectedHmac, $actualHmac),
        "The returned '$actualHmac' HMAC value does not match the expected '$expectedHmac' value."
    );
});

it('throws an error if the secret is not set', function () {
    config(['belltastic.projects.'.$this->user->project_id.'.secret' => '']);

    try {
        /** @noinspection */
        $actualHmac = User::hmac($this->user->project_id, $this->user->id);
    } catch (MissingSecretException $exception) {
        assertEquals(MissingSecretException::DEFAULT_MESSAGE, $exception->getMessage());
        return;
    }

    $this->fail('Exception was not thrown due to missing secret.');
});
