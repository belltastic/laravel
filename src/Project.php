<?php

namespace Belltastic;

class Project extends ApiResource
{
    const OBJECT_NAME = 'project';

    use ApiOperations\All;
    use ApiOperations\Find;
    use ApiOperations\Create;
    use ApiOperations\Update;

    protected $paginated = false;

    public function listUrl(): string
    {
        return 'v1/projects';
    }

    public function instanceUrl(): string
    {
        return "v1/project/{$this->id}";
    }
}
