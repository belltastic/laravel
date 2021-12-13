<?php

use Belltastic\Project;
use Belltastic\User;
use Illuminate\Support\LazyCollection;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

it('can list all users from the project object', function () {
    $project = new Project(loadTestFile('test_data/single_project.json'));
    $usersData = loadTestFile('test_data/multiple_users.json');
    queueMockResponse(200, $usersData);

    $users = $project->users()->all();

    assertInstanceOf(LazyCollection::class, $users);
    // Because it's a lazy collection, there won't be a single request at first
    assertRequestCount(0);
    $users->first();    // trigger lazy collection iteration
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/'.$project->id.'/users'
    );
});

it('can find an individual user from a project', function () {
    $project = new Project(loadTestFile('test_data/single_project.json'));
    $userData = loadTestFile('test_data/single_user.json');
    queueMockResponse(200, $userData);

    $user = $project->users()->find($userData['id']);

    assertInstanceOf(User::class, $user);
    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'get',
        '/api/v1/project/'.$project->id.'/user/'.$userData['id']
    );
});

it('can create a user from within the project', function () {
    $project = new Project(loadTestFile('test_data/single_project.json'));
    $userData = [
        'id' => 'new-user-id',
        'name' => 'Test user name',
        'email' => 'testemail@example.com',
    ];
    queueMockResponse(404, ['message' => 'User not found.']);
    queueMockResponse(201, [
        'message' => 'User created.',
        'data' => loadTestFile('test_data/single_user.json', $userData),
    ]);

    $user = $project->users()->create($userData);

    assertInstanceOf(User::class, $user);
    assertRequestCount(2);
    assertRequestIs(
        getLastRequest(),
        'put',
        '/api/v1/project/'.$project->id.'/user/'.$userData['id'],
        array_merge($userData, ['project_id' => $project->id])
    );
    assertEquals($userData['name'], $user->name);
    assertEquals($userData['email'], $user->email);
});
