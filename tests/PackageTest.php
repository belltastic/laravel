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

    expect($project)->toBeInstanceOf(Project::class);

    foreach (SINGLE_PROJECT_DATA as $key => $value) {
        expect($project[$key])->toBe($value);
    }
});

it('can return multiple packages', function () {
    $this->queueMockResponse(200, MULTIPLE_PROJECTS_DATA);

    $projects = Project::all();

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

    assertCount(1, $this->requestHistory);
    /** @var \GuzzleHttp\Psr7\Request $request */
    list('request' => $request, 'response' => $response) = $this->requestHistory[0];
    assertEquals('/api/v1/projects', $request->getUri()->getPath());
    assertEquals(json_encode($requestData), (string) $request->getBody());
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

    assertCount(1, $this->requestHistory);
    /** @var \GuzzleHttp\Psr7\Request $request */
    list('request' => $request) = $this->requestHistory[0];
    assertEquals('/api/v1/project/'.SINGLE_PROJECT_DATA['id'], $request->getUri()->getPath());
    assertEquals(json_encode(array_merge(SINGLE_PROJECT_DATA, $newData)), (string) $request->getBody());
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

    assertCount(1, $this->requestHistory);
    /** @var \GuzzleHttp\Psr7\Request $request */
    list('request' => $request) = $this->requestHistory[0];
    assertEquals('/api/v1/project/'.SINGLE_PROJECT_DATA['id'], $request->getUri()->getPath());
    assertEquals(json_encode(array_merge(SINGLE_PROJECT_DATA, $newData)), (string) $request->getBody());
});
