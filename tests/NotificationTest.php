<?php

use Belltastic\Notification;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertStringContainsString;

beforeEach(function () {
    $this->project_id = 1;
    $this->user_id = 123;
    $this->routeBase = '/api/v1/project/' . $this->project_id . '/user/' . $this->user_id;
    $this->singleNotificationData = [
        'id' => '9505f16b-3ac2-48ed-86d8-395544ddc735',
        'project_id' => $this->project_id,
        'user_id' => $this->user_id,
        'icon' => 'https:\/\/via.placeholder.com\/80x80.png\/0033ff?text=sapiente',
        'title' => 'First notification title',
        'body' => 'First notification body. With a lot of text.',
        'category' => 'system',
        'action_url' => null,
        'seen_at' => null,
        'read_at' => null,
        'created_at' => '2021-11-19T06:11:45+00:00',
    ];
    $this->multipleNotificationsData = [
        'data' => [
            [
                'id' => '9505f16b-3a3e-461c-b851-b7547b097ab2',
                'project_id' => $this->project_id,
                'user_id' => $this->user_id,
                'icon' => 'https:\/\/via.placeholder.com\/80x80.png\/002244?text=minima',
                'title' => 'ratione esse et omnis',
                'body' => 'Dolores corporis ut saepe et quibusdam at. Voluptas itaque ea molestiae mollitia suscipit fugit ratione similique. Quidem et ut modi sint quia quia.',
                'category' => 'system',
                'action_url' => null,
                'seen_at' => null,
                'read_at' => null,
                'created_at' => '2021-11-24T01:55:36+00:00',
            ],
            [
                'id' => '9505f16b-39ff-42a0-bd4c-5b9b784f2a63',
                'project_id' => $this->project_id,
                'user_id' => $this->user_id,
                'icon' => 'https:\/\/via.placeholder.com\/80x80.png\/001133?text=et',
                'title' => 'numquam inventore commodi repudiandae',
                'body' => 'Commodi et architecto labore voluptas velit ipsa. Incidunt laudantium deserunt esse. Rerum deserunt ipsum ut harum quo qui mollitia.',
                'category' => 'system',
                'action_url' => 'http:\/\/west.com\/vitae-sed-nostrum-sint',
                'seen_at' => null,
                'read_at' => null,
                'created_at' => '2021-11-18T01:31:12+00:00',
            ],
        ],
        'links' => [
            'first' => null,
            'last' => null,
            'prev' => null,
            'next' => 'http:\/\/belltastic.test\/api\/v1\/project\/1\/user\/123\/notifications?per_page=10&order=desc&cursor=eyJpZCI6Ijk1MDVmMTZiLTM4NmQtNGVjNi05NzY5LWZkYTY2NTliZDIyMyIsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0',
        ],
        'meta' => [
            'path' => 'http:\/\/belltastic.test\/api\/v1\/project\/1\/user\/123\/notifications',
            'per_page' => 10,
        ],
    ];
});

it('can get individual notification', function () {
    queueMockResponse(200, $this->singleNotificationData);

    $notification = Notification::find($this->project_id, $this->user_id, $this->singleNotificationData['id']);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        $this->routeBase . '/notification/' . $this->singleNotificationData['id']
    );
    foreach ($this->singleNotificationData as $key => $value) {
        assertEquals($value, $notification[$key]);
    }
});

it('can list all notifications', function () {
    queueMockResponse(200, $this->multipleNotificationsData);
    // second page
    queueMockResponse(200, [
        'data' => [$this->singleNotificationData],
        'links' => ['next' => null],
        'meta' => [],
    ]);

    $notifications = Notification::all($this->project_id, $this->user_id);

    // Because it's a Lazy collection, the requests won't fire until at least one element
    // is requested from the collection.
    assertRequestCount(0);
    assertInstanceOf(LazyCollection::class, $notifications);

    $firstNotification = $notifications->first();
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', $this->routeBase . '/notifications');

    $notificationModels = $notifications->all();
    // another request should've been fired now, because the first request returned a link to the next page
    // and calling ->all() on a lazy collection iterates through all the possible entries
    assertRequestCount(2);
    assertCount(3, $notificationModels);

    clearRequests();
    $count = $notifications->count();
    // since we retrieved the full collection already with the ->all() method previously,
    // calling ->count() should make no additional requests.
    assertRequestCount(0);
});

it('can create a new notification', function () {
    $notificationData = [
        'title' => 'Example title',
        'body' => 'The body text of the notification',
        'action_url' => 'https://belltastic.com/register',
    ];
    queueMockResponse(201, array_merge($this->singleNotificationData, $notificationData));

    $notification = Notification::create($this->project_id, $this->user_id, $notificationData);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->routeBase . '/notifications', $notificationData);

    foreach (array_merge($this->singleNotificationData, $notificationData) as $key => $value) {
        assertEquals($value, $notification[$key]);
    }
});

it('cannot update or save notifications like other objects', function () {
    $notification = new Notification($this->singleNotificationData);
    queueMockResponse(200, $this->singleNotificationData);

    try {
        $notification->update(['title' => 'New title']);
        $this->fail('Method not found exception was not thrown. It should not be possible to update the notification like other objects.');
    } catch (\Error $error) {
        assertStringContainsString('Call to undefined method', $error->getMessage());
        assertRequestCount(0);
    }

    try {
        $notification->title = 'new title';
        $notification->save();
        $this->fail('Method not found exception was not thrown. It should not be possible to save the notification manually like other objects.');
    } catch (\Error $error) {
        assertStringContainsString('Call to undefined method', $error->getMessage());
        assertRequestCount(0);
    }
});

it('can soft delete a notification', function () {
    $user = new Notification($this->singleNotificationData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'Notification archived',
        'data' => array_merge($this->singleNotificationData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    $user->delete();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/notification/' . $this->singleNotificationData['id'],
        []
    );
    assertEquals($deletedAt, $user->deleted_at);
});

it('can force delete a notification', function () {
    $user = new Notification($this->singleNotificationData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'Notification deleted',
        'data' => array_merge($this->singleNotificationData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    $user->forceDelete();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/notification/' . $this->singleNotificationData['id'],
        ['force' => true]
    );
    assertEquals($deletedAt, $user->deleted_at);
});

it('can mark notification as read', function () {
    $notification = new Notification($this->singleNotificationData);
    $readAt = now()->micro(0);
    queueMockResponse(200, array_merge($this->singleNotificationData, ['read_at' => $readAt->toIso8601String()]));

    $notification->markAsRead();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'post',
        $this->routeBase . '/notification/'.$this->singleNotificationData['id'].'/read',
        []
    );
    assertEquals($readAt, $notification->read_at);
});

it('can mark notification as unread', function () {
    $notification = new Notification(array_merge($this->singleNotificationData, ['read_at' => now()->subHour()]));
    queueMockResponse(200, array_merge($this->singleNotificationData, ['read_at' => null]));

    $notification->markAsUnread();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/notification/'.$this->singleNotificationData['id'].'/read',
        []
    );
    assertEquals(null, $notification->read_at);
});

it('can mark notification as seen', function () {
    $notification = new Notification($this->singleNotificationData);
    $seenAt = now()->micro(0);
    queueMockResponse(200, array_merge($this->singleNotificationData, ['seen_at' => $seenAt->toIso8601String()]));

    $notification->markAsSeen();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'post',
        $this->routeBase . '/notification/'.$this->singleNotificationData['id'].'/seen',
        []
    );
    assertEquals($seenAt, $notification->seen_at);
});
