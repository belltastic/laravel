<?php

namespace Belltastic;

use Illuminate\Support\Facades\Date;

class Notification extends ApiResource
{
    use ApiOperations\Find;
    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;

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

    public static function create($project_id, $user_id, $attributes = [], $options = [])
    {
        return (new static(['project_id' => $project_id, 'user_id' => $user_id]))->_create($attributes, $options);
    }

    public function markAsSeen($options = []): void
    {
        $client = new ApiClient($options['api_key'] ?? null, $options);

        $response = $client->post($this->instanceUrl().'/seen');

        $this->fill([
            'seen_at' => $response['seen_at'] ? Date::parse($response['seen_at']) : null,
        ]);
    }

    public function markAsRead($options = []): void
    {
        $client = new ApiClient($options['api_key'] ?? null, $options);

        $response = $client->post($this->instanceUrl().'/read');

        $this->fill([
            'read_at' => $response['read_at'] ? Date::parse($response['read_at']) : null,
        ]);
    }

    public function markAsUnread($options = []): void
    {
        $client = new ApiClient($options['api_key'] ?? null, $options);

        $response = $client->delete($this->instanceUrl().'/read');

        $this->fill([
            'read_at' => $response['read_at'] ? Date::parse($response['read_at']) : null,
        ]);
    }
}
