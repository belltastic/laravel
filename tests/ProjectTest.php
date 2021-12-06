<?php

use Belltastic\Project;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;

const SINGLE_PROJECT_DATA = [
    "id" => 1,
    "name" => "Default",
    "default" => false,
    "hmac_required" => false,
    "team_id" => 1,
];
const MULTIPLE_PROJECTS_DATA = [
    [
        "id" => 1,
        "name" => "Default",
        "default" => false,
        "hmac_required" => false,
        "team_id" => 1,
    ],
    [
        "id" => 2,
        "name" => "Second project",
        "default" => true,
        "hmac_required" => false,
        "team_id" => 1,
    ],
];

it('can return a single package', function () {
    $this->queueMockResponse(200, SINGLE_PROJECT_DATA);

    $project = Project::find(SINGLE_PROJECT_DATA['id']);

    assertRequestIs(getFirstRequest(), 'get', '/api/v1/project/'.SINGLE_PROJECT_DATA['id']);
    expect($project)->toBeInstanceOf(Project::class);
    foreach (SINGLE_PROJECT_DATA as $key => $value) {
        expect($project[$key])->toBe($value);
    }
});

it('can return multiple packages', function () {
    $this->queueMockResponse(200, MULTIPLE_PROJECTS_DATA);

    $projects = Project::all();

    assertRequestIs(getFirstRequest(), 'get', '/api/v1/projects');
    expect($projects)->toBeCollection();
    assertCount(2, $projects);
    assertInstanceOf(Project::class, $projects[0]);
    assertInstanceOf(Project::class, $projects[1]);
    foreach (MULTIPLE_PROJECTS_DATA[0] as $key => $value) {
        assertEquals($value, $projects[0][$key]);
    }
    foreach (MULTIPLE_PROJECTS_DATA[1] as $key => $value) {
        assertEquals($value, $projects[1][$key]);
    }
});

it('can create a new project', function () {
    $requestData = [
        'name' => 'New Project',
        'default' => true,
    ];
    $this->queueMockResponse(201, array_merge(SINGLE_PROJECT_DATA, $requestData));

    $project = Project::create($requestData);

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
    $project = new Project(SINGLE_PROJECT_DATA);
    $this->queueMockResponse(200, array_merge(SINGLE_PROJECT_DATA, $newData));

    $project->update($newData);

    assertRequestIs(
        getFirstRequest(),
        'put',
        '/api/v1/project/'.SINGLE_PROJECT_DATA['id'],
        array_merge(SINGLE_PROJECT_DATA, $newData)
    );
});

it('can update a project by calling save() method', function () {
    $newData = [
        'name' => 'Updated name',
        'default' => true,
    ];
    $project = new Project(SINGLE_PROJECT_DATA);
    $this->queueMockResponse(200, array_merge(SINGLE_PROJECT_DATA, $newData));

    $project->name = $newData['name'];
    $project->default = $newData['default'];
    $project->save();

    assertRequestIs(
        getFirstRequest(),
        'put',
        '/api/v1/project/'.SINGLE_PROJECT_DATA['id'],
        array_merge(SINGLE_PROJECT_DATA, $newData)
    );
});

it('can soft delete a project', function () {
    $project = new Project(SINGLE_PROJECT_DATA);
    $deletedAt = now()->micro(0);
    $this->queueMockResponse(200, [
        'message' => 'Project archived',
        'data' => array_merge(SINGLE_PROJECT_DATA, ['deleted_at' => $deletedAt->toIso8601String()])
    ]);

    $project->delete();

    assertRequestIs(getFirstRequest(), 'delete', '/api/v1/project/'.SINGLE_PROJECT_DATA['id'], []);
    assertEquals($deletedAt, $project->deleted_at);
});

it('can force delete a project', function () {
    $project = new Project(SINGLE_PROJECT_DATA);
    $deletedAt = now()->micro(0);
    $this->queueMockResponse(200, [
        'message' => 'Project deleted',
        'data' => array_merge(SINGLE_PROJECT_DATA, ['deleted_at' => $deletedAt->toIso8601String()])
    ]);

    $project->forceDelete();

    assertRequestIs(
        getFirstRequest(),
        'delete',
        '/api/v1/project/'.SINGLE_PROJECT_DATA['id'],
        ['force' => true]
    );
    assertEquals($deletedAt, $project->deleted_at);
});
