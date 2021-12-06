<?php

namespace Belltastic;

class Project extends ApiResource
{
    use ApiOperations\All;
    use ApiOperations\Find;
    use ApiOperations\Create;
    use ApiOperations\Update;
    use ApiOperations\Delete;

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
