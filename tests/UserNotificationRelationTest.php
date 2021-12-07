<?php

use Belltastic\Notification;
use Belltastic\User;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

it('can list all notifications from the user object', function () {
    $user = new User(loadTestFile('test_data/single_user.json'));
    $notificationsData = loadTestFile('test_data/multiple_notifications.json');
    queueMockResponse(200, $notificationsData);

    $notifications = $user->notifications()->all();

    assertInstanceOf(LazyCollection::class, $notifications);
    // Because it's a lazy collection, there won't be a single request at first
    assertRequestCount(0);
    $notification = $notifications->first();    // trigger lazy collection iteration
    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/'.$user->project_id.'/user/'.$user->id.'/notifications'
    );
});

it('can find an individual notification from a user', function () {
    $user = new User(loadTestFile('test_data/single_user.json'));
    $notificationData = loadTestFile('test_data/single_notification.json');
    queueMockResponse(200, $notificationData);

    $notification = $user->notifications()->find($notificationData['id']);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/'.$user->project_id.'/user/'.$user->id.'/notification/'.$notificationData['id']
    );
});

it('can create a notification from within the user', function () {
    $user = new User(loadTestFile('test_data/single_user.json'));
    $notificationData = [
        'title' => 'Test notification title',
        'body' => 'Notification body',
    ];
    queueMockResponse(200, loadTestFile('test_data/single_notification.json', $notificationData));

    $notification = $user->notifications()->create($notificationData);

    assertInstanceOf(Notification::class, $notification);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'post',
        '/api/v1/project/'.$user->project_id.'/user/'.$user->id.'/notifications',
        $notificationData
    );
    assertEquals($notificationData['title'], $notification->title);
    assertEquals($notificationData['body'], $notification->body);
});
