<?php

use Belltastic\Notification;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertStringContainsString;

beforeEach(function () {
    $createdAt = now()->subHour()->micro(0);
    $this->project_id = 1;
    $this->user_id = 123;
    $this->routeBase = '/api/v1/project/' . $this->project_id . '/user/' . $this->user_id;
    $this->singleNotificationData = loadTestFile('test_data/single_notification.json', [
        'user_id' => $this->user_id,
        'project_id' => $this->project_id,
        'created_at' => $createdAt->toIso8601String(),
    ]);
});

it('can get individual notification', function () {
    queueMockResponse(200, $this->singleNotificationData);

    /** @noinspection PhpUnhandledExceptionInspection */
    $notification = Notification::find($this->project_id, $this->user_id, $this->singleNotificationData['id']);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        $this->routeBase . '/notification/' . $this->singleNotificationData['id']
    );
    assertEquals($this->singleNotificationData, $notification->toFlatArray());
});

it('can list all notifications', function () {
    $multipleNotificationsData = loadTestFile('test_data/multiple_notifications.json');
    queueMockResponse(200, $multipleNotificationsData);
    // second page
    queueMockResponse(200, [
        'data' => [$this->singleNotificationData],
        'links' => ['next' => null],
        'meta' => [],
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $notifications = Notification::all($this->project_id, $this->user_id);

    // Because it's a Lazy collection, the requests won't fire until at least one element
    // is requested from the collection.
    assertRequestCount(0);
    assertInstanceOf(LazyCollection::class, $notifications);

    $notifications->first();
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', $this->routeBase . '/notifications');

    $notificationModels = $notifications->all();
    // another request should've been fired now, because the first request returned a link to the next page
    // and calling ->all() on a lazy collection iterates through all the possible entries
    assertRequestCount(2);
    assertCount(3, $notificationModels);

    clearRequests();
    $notifications->count();
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

    /** @noinspection PhpUnhandledExceptionInspection */
    $notification = Notification::create($this->project_id, $this->user_id, $notificationData);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->routeBase . '/notifications', $notificationData);
    assertEquals(array_merge($this->singleNotificationData, $notificationData), $notification->toFlatArray());
});

it('cannot update or save notifications like other objects', function () {
    $notification = new Notification($this->singleNotificationData);
    queueMockResponse(200, $this->singleNotificationData);

    try {
        /** @noinspection PhpUndefinedMethodInspection */
        $notification->update(['title' => 'New title']);
        $this->fail('"Method not found" exception was not thrown. It should not be possible to update the notification like other objects.');
    } catch (Error $error) {
        assertStringContainsString('Call to undefined method', $error->getMessage());
        assertRequestCount(0);
    }

    try {
        $notification->setAttribute('title', 'new title');
        /** @noinspection PhpUndefinedMethodInspection */
        $notification->save();
        $this->fail('"Method not found" exception was not thrown. It should not be possible to save the notification manually like other objects.');
    } catch (Error $error) {
        assertStringContainsString('Call to undefined method', $error->getMessage());
        assertRequestCount(0);
    }
});

it('can archive a notification', function () {
    $user = new Notification($this->singleNotificationData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'Notification archived.',
        'data' => array_merge($this->singleNotificationData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user->archive();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        $this->routeBase . '/notification/' . $this->singleNotificationData['id'] . '/archive',
        []
    );
    assertEquals($deletedAt, $user->deleted_at);
});

it('can force delete a notification', function () {
    $user = new Notification($this->singleNotificationData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'Notification deleted.',
        'data' => array_merge($this->singleNotificationData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $user->destroy();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        $this->routeBase . '/notification/' . $this->singleNotificationData['id'],
        []
    );
    assertEquals($deletedAt, $user->deleted_at);
});

it('can mark notification as read', function () {
    $notification = new Notification($this->singleNotificationData);
    $readAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'Notification updated.',
        'data' => array_merge($this->singleNotificationData, ['read_at' => $readAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
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
    queueMockResponse(200, [
        'message' => 'Notification updated.',
        'data' => array_merge($this->singleNotificationData, ['read_at' => null]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
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
    queueMockResponse(200, [
        'message' => 'Notification updated.',
        'data' => array_merge($this->singleNotificationData, ['seen_at' => $seenAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
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
