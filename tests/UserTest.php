<?php

use Belltastic\User;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

const PROJECT_ID = 1;
const SINGLE_USER_DATA = [
    "id" => "54a1ef31-5119-3fdc-b98f-9872357e2801",
    "belltastic_id" => 23,
    "email" => "test@example.com",
    "name" => "Test User",
    "avatar_url" => "https:\/\/via.placeholder.com\/80x80.png\/007744?text=test+user",
    "project_id" => PROJECT_ID,
    "unread_notifications_count" => 20,
];
const MULTIPLE_USERS_DATA = [
    "data" => [
        [
            "id" => "05e9d499-4dd6-3ef7-83b5-a1328bb1cff1",
            "belltastic_id" => 15,
            "email" => "john@example.com",
            "name" => "John Doe",
            "avatar_url" => "https:\/\/via.placeholder.com\/80x80.png\/0033aa?text=john+doe",
            "project_id" => 1,
            "unread_notifications_count" => 40,
        ],
        [
            "id" => "19a1ef31-5119-3fdc-b98f-9872357e2801",
            "belltastic_id" => 29,
            "email" => "jane@example.com",
            "name" => "Jane Doe",
            "avatar_url" => "https:\/\/via.placeholder.com\/80x80.png\/007744?text=jane+doe",
            "project_id" => 1,
            "unread_notifications_count" => 40,
        ],
    ],
    "links" => [
        "first" => null,
        "last" => null,
        "prev" => null,
        "next" => "https:\/\/belltastic.com\/api\/v1\/project\/1\/users?cursor=eyJleHRlcm5hbF9pZCI6IjUzNmU1OWVjLTkyYzItMzU0Ni1iZDA3LWU3NzUzNmZhZjYzYSIsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
    ],
    "meta" => [
        "path" => "https:\/\/belltastic.com\/api\/v1\/project\/1\/users",
        "per_page" => 10,
    ],
];

it('can retrieve a single user', function () {
    queueMockResponse(200, SINGLE_USER_DATA);

    $user = User::find(PROJECT_ID, SINGLE_USER_DATA['id']);

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/' . PROJECT_ID . '/user/' . SINGLE_USER_DATA['id']
    );
    assertInstanceOf(User::class, $user);

    foreach (SINGLE_USER_DATA as $key => $value) {
        assertEquals($value, $user[$key]);
    }
});

it('can retrieve multiple users', function () {
    queueMockResponse(200, MULTIPLE_USERS_DATA);
    // second page
    queueMockResponse(200, [
        'data' => [
            SINGLE_USER_DATA,
        ],
        'links' => [
            'next' => null,
        ],
        'meta' => [],
    ]);

    $users = User::all(PROJECT_ID);

    // Because it's a Lazy collection, the requests won't fire until at least one element
    // is requested from the collection.
    assertRequestCount(0);
    assertInstanceOf(LazyCollection::class, $users);

    $firstUser = $users->first();
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/' . PROJECT_ID . '/users'
    );

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
    $userData = [
        'name' => 'Test name',
    ];
    queueMockResponse(201, array_merge(SINGLE_USER_DATA, $userData));

    $user = User::create(PROJECT_ID, $userData);

    assertInstanceOf(User::class, $user);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'post',
        '/api/v1/project/'.PROJECT_ID.'/users',
        $userData
    );
    assertEquals('Test name', $user->name);
    assertEquals(PROJECT_ID, $user->project_id);
});
