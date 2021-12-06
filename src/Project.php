<?php

namespace Belltastic;

class Project extends ApiResource
{
    use ApiOperations\All;
    use ApiOperations\Find;
    use ApiOperations\Create;
    use ApiOperations\Update;
    use ApiOperations\Delete;

    public const OBJECT_NAME = 'project';

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
