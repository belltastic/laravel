<?php

use Belltastic\User;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    config(['belltastic.api_key' => 'valid-key']);
    $createdAt = now()->subHour()->micro(0);
    $this->project_id = 1;
    $this->routeBase = '/api/v1/project/' . $this->project_id;
    $this->singleUserData = loadTestFile('test_data/single_user.json');
    $this->singleUserData['project_id'] = $this->project_id;
    $this->singleUserData['created_at'] = $createdAt->toIso8601String();
});

it('can retrieve a single user', function () {
    queueMockResponse(200, $this->singleUserData);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user = User::find($this->project_id, $this->singleUserData['id']);

    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', $this->routeBase . '/user/' . $this->singleUserData['id']);
    assertInstanceOf(User::class, $user);
    assertEquals($this->singleUserData, $user->toFlatArray());
});

it('can retrieve multiple users', function () {
    $multipleUsersData = loadTestFile('test_data/multiple_users.json');
    queueMockResponse(200, $multipleUsersData);
    // second page
    queueMockResponse(200, [
        'data' => [$this->singleUserData],
        'links' => ['next' => null],
        'meta' => [],
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $users = User::all($this->project_id);

    // Because it's a Lazy collection, the requests won't fire until at least one element
    // is requested from the collection.
    assertRequestCount(0);
    assertInstanceOf(LazyCollection::class, $users);

    $users->first();
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', $this->routeBase . '/users');

    $userModels = $users->all();
    // another request should've been fired now, because the first request returned a link to the next page
    // and calling ->all() on a lazy collection iterates through all the possible entries
    assertRequestCount(2);
    assertCount(3, $userModels);

    // since we retrieved the full collection already with the ->all() method,
    // calling ->count() should make no additional requests.
    clearRequests();
    $users->count();

    assertRequestCount(0);
});

it('can create a user', function () {
    $userData = ['id' => 'new-id', 'name' => 'Test name'];
    queueMockResponse(404, ['message' => 'User not found.']);
    queueMockResponse(201, [
        'message' => 'User created.',
        'data' => array_merge($this->singleUserData, $userData),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user = User::create($this->project_id, $userData);

    assertInstanceOf(User::class, $user);
    assertRequestCount(2);
    assertRequestIs(
        getLastRequest(),
        'put',
        $this->routeBase . '/user/'.$userData['id'],
        array_merge($userData, ['project_id' => $this->project_id])
    );
    assertEquals('Test name', $user->name);
    assertEquals($this->project_id, $user->project_id);
    assertEquals(array_merge($this->singleUserData, $userData), $user->toFlatArray());
});

it('can update user with the update() method', function () {
    $newData = [
        'name' => 'New name',
        'email' => 'newemail@example.com',
    ];
    queueMockResponse(200, [
        'message' => 'User updated.',
        'data' => array_merge($this->singleUserData, $newData),
    ]);
    $user = new User($this->singleUserData);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user->update($newData);

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        $this->routeBase . '/user/' . $this->singleUserData['id'],
        array_merge($this->singleUserData, $newData)
    );
});

it('can update user with the save() method', function () {
    $newData = [
        'name' => 'New name',
        'email' => 'newemail@example.com',
    ];
    queueMockResponse(200, [
        'message ' => 'User updated.',
        'data' => array_merge($this->singleUserData, $newData),
    ]);
    $user = new User($this->singleUserData);

    $user->name = $newData['name'];
    $user->email = $newData['email'];
    /** @noinspection PhpUnhandledExceptionInspection */
    $user->save();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        $this->routeBase . '/user/' . $this->singleUserData['id'],
        array_merge($this->singleUserData, $newData)
    );
});

it('can archive a user', function () {
    $user = new User($this->singleUserData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'User archived.',
        'data' => array_merge($this->singleUserData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user->archive();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        $this->routeBase . '/user/' . $this->singleUserData['id'] . '/archive',
        []
    );
    assertEquals($deletedAt, $user->deleted_at);
});

it('can force delete a user', function () {
    $user = new User($this->singleUserData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'User deleted.',
        'data' => array_merge($this->singleUserData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user->destroy();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/user/' . $this->singleUserData['id'],
        []
    );
    assertEquals($deletedAt, $user->deleted_at);
});
