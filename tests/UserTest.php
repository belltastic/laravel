<?php

use Belltastic\User;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    $this->project_id = 1;
    $this->routeBase = '/api/v1/project/' . $this->project_id;
    $this->singleUserData = [
        'id' => '54a1ef31-5119-3fdc-b98f-9872357e2801',
        'belltastic_id' => 23,
        'email' => 'test@example.com',
        'name' => 'Test User',
        'avatar_url' => 'https:\/\/via.placeholder.com\/80x80.png\/007744?text=test+user',
        'project_id' => $this->project_id,
        'unread_notifications_count' => 20,
    ];
    $this->multipleUsersData = [
        'data' => [
            [
                'id' => '05e9d499-4dd6-3ef7-83b5-a1328bb1cff1',
                'belltastic_id' => 15,
                'email' => 'john@example.com',
                'name' => 'John Doe',
                'avatar_url' => 'https:\/\/via.placeholder.com\/80x80.png\/0033aa?text=john+doe',
                'project_id' => $this->project_id,
                'unread_notifications_count' => 40,
            ],
            [
                'id' => '19a1ef31-5119-3fdc-b98f-9872357e2801',
                'belltastic_id' => 29,
                'email' => 'jane@example.com',
                'name' => 'Jane Doe',
                'avatar_url' => 'https:\/\/via.placeholder.com\/80x80.png\/007744?text=jane+doe',
                'project_id' => $this->project_id,
                'unread_notifications_count' => 40,
            ],
        ],
        'links' => [
            'first' => null,
            'last' => null,
            'prev' => null,
            'next' => 'https:\/\/belltastic.com\/api\/v1\/project\/1\/users?cursor=eyJleHRlcm5hbF9pZCI6IjUzNmU1OWVjLTkyYzItMzU0Ni1iZDA3LWU3NzUzNmZhZjYzYSIsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0',
        ],
        'meta' => [
            'path' => 'https:\/\/belltastic.com\/api\/v1\/project\/1\/users',
            'per_page' => 10,
        ],
    ];
});

it('can retrieve a single user', function () {
    queueMockResponse(200, $this->singleUserData);

    $user = User::find($this->project_id, $this->singleUserData['id']);

    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', $this->routeBase . '/user/' . $this->singleUserData['id']);
    assertInstanceOf(User::class, $user);

    foreach ($this->singleUserData as $key => $value) {
        assertEquals($value, $user[$key]);
    }
});

it('can retrieve multiple users', function () {
    queueMockResponse(200, $this->multipleUsersData);
    // second page
    queueMockResponse(200, [
        'data' => [$this->singleUserData],
        'links' => ['next' => null],
        'meta' => [],
    ]);

    $users = User::all($this->project_id);

    // Because it's a Lazy collection, the requests won't fire until at least one element
    // is requested from the collection.
    assertRequestCount(0);
    assertInstanceOf(LazyCollection::class, $users);

    $firstUser = $users->first();
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
    $count = $users->count();

    assertRequestCount(0);
});

it('can create a user', function () {
    $userData = ['name' => 'Test name'];
    queueMockResponse(201, array_merge($this->singleUserData, $userData));

    $user = User::create($this->project_id, $userData);

    assertInstanceOf(User::class, $user);
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->routeBase . '/users', $userData);
    assertEquals('Test name', $user->name);
    assertEquals($this->project_id, $user->project_id);
});

it('can update user with the update() method', function () {
    $newData = [
        'name' => 'New name',
        'email' => 'newemail@example.com',
    ];
    queueMockResponse(200, array_merge($this->singleUserData, $newData));
    $user = new User($this->singleUserData);

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
    queueMockResponse(200, array_merge($this->singleUserData, $newData));
    $user = new User($this->singleUserData);

    $user->name = $newData['name'];
    $user->email = $newData['email'];
    $user->save();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        $this->routeBase . '/user/' . $this->singleUserData['id'],
        array_merge($this->singleUserData, $newData)
    );
});

it('can soft delete a user', function () {
    $user = new User($this->singleUserData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'User archived',
        'data' => array_merge($this->singleUserData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    $user->delete();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/user/' . $this->singleUserData['id'],
        []
    );
    assertEquals($deletedAt, $user->deleted_at);
});

it('can force delete a user', function () {
    $user = new User($this->singleUserData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'User deleted',
        'data' => array_merge($this->singleUserData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    $user->forceDelete();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/user/' . $this->singleUserData['id'],
        ['force' => true]
    );
    assertEquals($deletedAt, $user->deleted_at);
});
