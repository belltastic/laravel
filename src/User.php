<?php

namespace Belltastic;

class User extends ApiResource
{
    use ApiOperations\Find;
    use ApiOperations\All;
    public const OBJECT_NAME = 'user';

    public function listUrl(): string
    {
        return "v1/project/{$this->project_id}/users";
    }

    public function instanceUrl(): string
    {
        return "v1/project/{$this->project_id}/user/{$this->id}";
    }

    public static function find($project_id, $id, $options = [])
    {
        $instance = new self(['project_id' => $project_id, 'id' => $id], $options);
        $instance->refresh();

        return $instance;
    }

    public static function all($project_id, $options = [])
    {
        $instance = new static(['project_id' => $project_id]);

        return $instance->_all($options);
    }
}
