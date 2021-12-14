<?php

namespace Belltastic;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;

/**
 * @package Belltastic
 *
 * @property-read string $id
 * @property-read int $project_id
 * @property-read string $user_id
 * @property-read string $icon
 * @property-read string $title
 * @property-read string $body
 * @property-read string $category
 * @property-read string $action_url
 * @property-read Carbon $created_at
 * @property-read Carbon|null $seen_at
 * @property-read Carbon|null $read_at
 * @property-read Carbon|null $deleted_at
 *
 * @method void archive(array $options = []) Archive (soft-delete) the notification.
 * @method void destroy(array $options = []) Delete the notification completely. There is no way to restore this.
 */
class Notification extends ApiResource
{
    use ApiOperations\Find;
    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Archive;
    use ApiOperations\Destroy;

    protected $fillable = [
        'project_id',
        'user_id',
        'title',
        'body',
        'icon',
        'category',
        'action_url',
    ];

    protected function listUrl(): string
    {
        return "project/$this->project_id/user/$this->user_id/notifications";
    }

    protected function instanceUrl(): string
    {
        return "project/$this->project_id/user/$this->user_id/notification/$this->id";
    }

    /**
     * @param int $project_id
     * @param string|int $user_id
     * @param string $id
     * @param array $options
     * @return Notification
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     * @throws GuzzleException
     */
    public static function find(int $project_id, $user_id, string $id, array $options = []): Notification
    {
        $instance = new static([
            'id' => $id,
            'project_id' => $project_id,
            'user_id' => strval($user_id),
        ], $options);
        $instance->refresh();

        return $instance;
    }

    /**
     * @param int $project_id
     * @param string|int $user_id
     * @param array $options
     * @return LazyCollection
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     * @throws GuzzleException
     */
    public static function all(int $project_id, $user_id, array $options = []): LazyCollection
    {
        return (new static([
            'project_id' => $project_id,
            'user_id' => strval($user_id),
        ]))->_all($options);
    }

    /**
     * @param int $project_id
     * @param string|int $user_id
     * @param array $attributes
     * @param array $options
     * @return Notification
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     * @throws GuzzleException
     */
    public static function create(int $project_id, $user_id, array $attributes = [], array $options = []): Notification
    {
        return (new static([
            'project_id' => $project_id,
            'user_id' => strval($user_id),
        ]))->_create($attributes, $options);
    }

    /**
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\UnauthorizedException
     * @throws GuzzleException
     * @throws Exceptions\ValidationException
     */
    public function markAsSeen($options = []): self
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options);
        $response = $client->post($this->instanceUrl().'/seen');
        $this->forceFill($response['data']);

        return $this;
    }

    /**
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\ForbiddenException
     * @throws GuzzleException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     */
    public function markAsRead($options = []): self
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options);
        $response = $client->post($this->instanceUrl().'/read');
        $this->forceFill($response['data']);

        return $this;
    }

    /**
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\ForbiddenException
     * @throws GuzzleException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     */
    public function markAsUnread($options = []): self
    {
        $client = new ApiClient($options['api_key'] ?? $this->_apiKey, $options);
        $response = $client->delete($this->instanceUrl().'/read');
        $this->forceFill($response['data']);

        return $this;
    }
}
