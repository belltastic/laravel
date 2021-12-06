<?php

use Belltastic\Notification;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

const PROJECT_ID = 1;
const USER_ID = 123;
const SINGLE_NOTIFICATION_DATA = [
    'id' => '9505f16b-3ac2-48ed-86d8-395544ddc735',
    'project_id' => PROJECT_ID,
    'user_id' => USER_ID,
    'icon' => 'https:\/\/via.placeholder.com\/80x80.png\/0033ff?text=sapiente',
    'title' => 'First notification title',
    'body' => 'First notification body. With a lot of text.',
    'category' => 'system',
    'action_url' => null,
    'seen_at' => null,
    'read_at' => null,
    'created_at' => '2021-11-19T06:11:45+00:00',
];
const MULTIPLE_NOTIFICATIONS_DATA = [
    "data" => [
        [
            "id" => "9505f16b-3a3e-461c-b851-b7547b097ab2",
            "project_id" => PROJECT_ID,
            "user_id" => USER_ID,
            "icon" => "https:\/\/via.placeholder.com\/80x80.png\/002244?text=minima",
            "title" => "ratione esse et omnis",
            "body" => "Dolores corporis ut saepe et quibusdam at. Voluptas itaque ea molestiae mollitia suscipit fugit ratione similique. Quidem et ut modi sint quia quia.",
            "category" => "system",
            "action_url" => null,
            "seen_at" => null,
            "read_at" => null,
            "created_at" => "2021-11-24T01:55:36+00:00",
        ],
        [
            "id" => "9505f16b-39ff-42a0-bd4c-5b9b784f2a63",
            "project_id" => PROJECT_ID,
            "user_id" => USER_ID,
            "icon" => "https:\/\/via.placeholder.com\/80x80.png\/001133?text=et",
            "title" => "numquam inventore commodi repudiandae",
            "body" => "Commodi et architecto labore voluptas velit ipsa. Incidunt laudantium deserunt esse. Rerum deserunt ipsum ut harum quo qui mollitia.",
            "category" => "system",
            "action_url" => "http:\/\/west.com\/vitae-sed-nostrum-sint",
            "seen_at" => null,
            "read_at" => null,
            "created_at" => "2021-11-18T01:31:12+00:00",
        ],
    ],
    "links" => [
        "first" => null,
        "last" => null,
        "prev" => null,
        "next" => "http:\/\/belltastic.test\/api\/v1\/project\/1\/user\/123\/notifications?per_page=10&order=desc&cursor=eyJpZCI6Ijk1MDVmMTZiLTM4NmQtNGVjNi05NzY5LWZkYTY2NTliZDIyMyIsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
    ],
    "meta" => [
        "path" => "http:\/\/belltastic.test\/api\/v1\/project\/1\/user\/123\/notifications",
        "per_page" => 10,
    ],
];

it('can get individual notification', function () {
    queueMockResponse(200, SINGLE_NOTIFICATION_DATA);

    $notification = Notification::find(PROJECT_ID, USER_ID, SINGLE_NOTIFICATION_DATA['id']);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/' . PROJECT_ID . '/user/' . USER_ID . '/notification/' . SINGLE_NOTIFICATION_DATA['id']
    );
    foreach (SINGLE_NOTIFICATION_DATA as $key => $value) {
        assertEquals($value, $notification[$key]);
    }
});

it('can list all notifications', function () {
    queueMockResponse(200, MULTIPLE_NOTIFICATIONS_DATA);
    // second page
    queueMockResponse(200, [
        'data' => [SINGLE_NOTIFICATION_DATA],
        'links' => ['next' => null],
        'meta' => [],
    ]);

    $notifications = Notification::all(PROJECT_ID, USER_ID);

    // Because it's a Lazy collection, the requests won't fire until at least one element
    // is requested from the collection.
    assertRequestCount(0);
    assertInstanceOf(LazyCollection::class, $notifications);

    $firstNotification = $notifications->first();
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/' . PROJECT_ID . '/user/' . USER_ID . '/notifications'
    );

    $notificationModels = $notifications->all();
    // another request should've been fired now, because the first request returned a link to the next page
    // and calling ->all() on a lazy collection iterates through all the possible entries
    assertRequestCount(2);
    assertCount(3, $notificationModels);

    // since we retrieved the full collection already with the ->all() method,
    // calling ->count() should make no additional requests.
    clearRequests();
    $count = $notifications->count();

    assertRequestCount(0);
});
