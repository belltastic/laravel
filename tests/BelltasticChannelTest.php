<?php

namespace Belltastic\Tests;

beforeEach(function () {
    config(['belltastic.api_key' => 'valid-key']);
    $this->notificationData = loadTestFile('test_data/single_notification.json');
    $this->expectedRoute = '/api/v1/project/'.$this->notificationData['project_id'].'/user/'.$this->notificationData['user_id'].'/notifications';
    $this->project_id = $this->notificationData['project_id'];
    $this->user_id = $this->notificationData['user_id'];
    $this->requestData = [
        'title' => 'Sample title',
        'body' => 'Sample body of the message',
    ];
});

it('does not send anything by merely creating new notification instance', function () {
    $appUser = new Stubs\AppUser([
        'project_id' => $this->project_id,
        'id' => $this->user_id,
    ]);

    $notification = new Stubs\SampleNotificationToBelltastic($this->requestData);

    assertRequestCount(0);
});

it('can route notification to BelltasticUser instance', function () {
    queueMockResponse(201, [
        'message' => 'Notification created.',
        'data' => array_merge($this->notificationData, $this->requestData),
    ]);
    $belltasticUser = new \Belltastic\User([
        'project_id' => $this->project_id,
        'id' => $this->user_id,
    ]);
    $appUser = new Stubs\AppUser($belltasticUser);

    // send a new notification
    $appUser->notify(
        $notification = new Stubs\SampleNotificationToBelltastic($this->requestData)
    );

    // expect the notification creation API route to be called
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->expectedRoute, $this->requestData);
});

it('can route notification to an array containing project id and user id', function () {
    queueMockResponse(201, [
        'message' => 'Notification created.',
        'data' => array_merge($this->notificationData, $this->requestData),
    ]);
    $appUser = new Stubs\AppUser([
        'project_id' => $this->project_id,
        'id' => $this->user_id,
    ]);

    // send a new notification
    $appUser->notify(
        $notification = new Stubs\SampleNotificationToBelltastic($this->requestData)
    );

    // expect the notification creation API route to be called
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->expectedRoute, $this->requestData);
});

it('can return a BelltasticNotification from the toBelltastic method', function () {
    queueMockResponse(201, [
        'message' => 'Notification created.',
        'data' => array_merge($this->notificationData, $this->requestData),
    ]);
    $appUser = new Stubs\AppUser([
        'project_id' => $this->project_id,
        'id' => $this->user_id,
    ]);
    $belltasticNotification = new \Belltastic\Notification($this->requestData);

    // send a new notification
    $appUser->notify(
        $notification = new Stubs\SampleNotificationToBelltastic($belltasticNotification)
    );

    // expect the notification creation API route to be called
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->expectedRoute, $this->requestData);
});

it('can utilise toArray method to get notification data', function () {
    queueMockResponse(201, [
        'message' => 'Notification created.',
        'data' => array_merge($this->notificationData, $this->requestData),
    ]);
    $appUser = new Stubs\AppUser([
        'project_id' => $this->project_id,
        'id' => $this->user_id,
    ]);

    // send a new notification
    $appUser->notify(
        $notification = new Stubs\SampleNotificationToArray($this->requestData)
    );

    // expect the notification creation API route to be called
    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', $this->expectedRoute, $this->requestData);
});
