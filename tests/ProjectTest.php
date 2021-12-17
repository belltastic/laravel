<?php

use Belltastic\Project;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    config(['belltastic.api_key' => 'valid-key']);
    $this->singleProjectData = loadTestFile('test_data/single_project.json');
});

it('can return a single project', function () {
    queueMockResponse(200, $this->singleProjectData);

    /** @noinspection PhpUnhandledExceptionInspection */
    $project = Project::find($this->singleProjectData['id']);

    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', '/api/v1/project/'.$this->singleProjectData['id']);
    expect($project)->toBeInstanceOf(Project::class);
    expect($project->toJson())->toBe(json_encode($this->singleProjectData));
});

it('can return multiple projects', function () {
    $multipleProjectData = loadTestFile('test_data/multiple_projects.json');
    queueMockResponse(200, $multipleProjectData);

    /** @noinspection PhpUnhandledExceptionInspection */
    $projects = Project::all();

    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'get', '/api/v1/projects');
    expect($projects)->toBeCollection();
    assertCount(2, $projects);
    assertInstanceOf(Project::class, $projects[0]);
    assertInstanceOf(Project::class, $projects[1]);
    assertEquals(json_encode($multipleProjectData[0]), $projects[0]->toJson());
    assertEquals(json_encode($multipleProjectData[1]), $projects[1]->toJson());
});

it('can create a new project', function () {
    $requestData = [
        'name' => 'New Project',
        'default' => true,
    ];
    queueMockResponse(201, [
        'message' => 'Project created.',
        'data' => array_merge($this->singleProjectData, $requestData),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $project = Project::create($requestData);

    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'post', '/api/v1/projects', $requestData);
    assertInstanceOf(Project::class, $project);
    assertEquals($requestData['name'], $project->name);
    assertEquals($requestData['default'], $project->default);
});

it('can update a project with the update() method', function () {
    $newData = [
        'name' => 'Updated name',
        'default' => true,
    ];
    $project = new Project($this->singleProjectData);
    queueMockResponse(200, [
        'message' => 'Project updated.',
        'data' => array_merge($this->singleProjectData, $newData),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $project->update($newData);

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        '/api/v1/project/'.$this->singleProjectData['id'],
        array_merge($this->singleProjectData, $newData)
    );
    assertEquals('Updated name', $project->name);
    assertEquals(true, $project->default);
});

it('can update a project by calling save() method', function () {
    $newData = [
        'name' => 'Updated name',
        'default' => true,
    ];
    $project = new Project($this->singleProjectData);
    queueMockResponse(200, [
        'message' => 'Project updated.',
        'data' => array_merge($this->singleProjectData, $newData),
    ]);

    $project->name = $newData['name'];
    $project->default = $newData['default'];
    /** @noinspection PhpUnhandledExceptionInspection */
    $project->save();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'put',
        '/api/v1/project/'.$this->singleProjectData['id'],
        array_merge($this->singleProjectData, $newData)
    );
});

it('can archive a project', function () {
    $project = new Project($this->singleProjectData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, [
        'message' => 'Project archived.',
        'data' => array_merge($this->singleProjectData, ['deleted_at' => $deletedAt->toIso8601String()]),
    ]);

    /** @noinspection PhpUnhandledExceptionInspection */
    $project->archive();

    assertRequestCount(1);
    assertRequestIs(getFirstRequest(), 'put', '/api/v1/project/'.$this->singleProjectData['id'].'/archive', []);
    assertEquals($deletedAt, $project->deleted_at);
});

it('can force delete a project', function () {
    $project = new Project($this->singleProjectData);
    $deletedAt = now()->micro(0);
    queueMockResponse(200, ['message' => 'Project deleted']);

    /** @noinspection PhpUnhandledExceptionInspection */
    $project->destroy();

    assertRequestCount(1);
    assertRequestIs(
        getFirstRequest(),
        'delete',
        '/api/v1/project/'.$this->singleProjectData['id'],
        []
    );
    assertEquals($deletedAt, $project->deleted_at);
});
