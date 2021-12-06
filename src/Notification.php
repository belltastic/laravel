<?php

namespace Belltastic;

class Notification extends ApiResource
{
    use ApiOperations\Find;
    use ApiOperations\All;

    public function listUrl(): string
    {
        return "v1/project/{$this->project_id}/user/{$this->user_id}/notifications";
    }

    public function instanceUrl(): string
    {
        return "v1/project/{$this->project_id}/user/{$this->user_id}/notification/{$this->id}";
    }

    public static function find($project_id, $user_id, $id, $options = [])
    {
        $instance = new static([
            'id' => $id,
            'project_id' => $project_id,
            'user_id' => $user_id,
        ], $options);
        $instance->refresh();

        return $instance;
    }

    public static function all($project_id, $user_id, $options = [])
    {
        return (new static(['project_id' => $project_id, 'user_id' => $user_id]))->_all($options);
    }
}
