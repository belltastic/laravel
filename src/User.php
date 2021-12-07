<?php

namespace Belltastic;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;

/**
 * @package Belltastic
 *
 * @property-read string $id
 * @property-read int $belltastic_id
 * @property-read int $project_id
 * @property string|null $email
 * @property string|null $name
 * @property string|null $avatar_url
 * @property-read int $unread_notifications_count
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $deleted_at
 *
 * @method $this update(array $attributes = [], array $options = []) Update the user with new values.
 * @method void save(array $options = []) Save the user's changes.
 * @method void delete(array $options = []) Archive (soft-delete) the user.
 * @method void forceDelete(array $options = []) Delete the user completely. This will also remove its related notifications. There is no way to restore this data.
 */
class User extends ApiResource
{
    use ApiOperations\Find;
    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Update;
    use ApiOperations\Delete;

    protected function listUrl(): string
    {
        return "v1/project/$this->project_id/users";
    }

    protected function instanceUrl(): string
    {
        return "v1/project/$this->project_id/user/$this->id";
    }

    /**
     * @param int $project_id
     * @param string|int $id
     * @param array $options
     * @return User
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     * @throws GuzzleException
     */
    public static function find(int $project_id, $id, array $options = []): User
    {
        $instance = new self(['project_id' => $project_id, 'id' => strval($id)], $options);
        $instance->refresh();

        return $instance;
    }

    /**
     * @param int $project_id
     * @param array $options
     * @return LazyCollection
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     * @throws GuzzleException
     */
    public static function all($project_id, array $options = []): LazyCollection
    {
        $instance = new static(['project_id' => $project_id]);

        return $instance->_all($options);
    }

    /**
     * @param int $project_id
     * @param array $attributes
     * @param array $options
     * @return User
     * @throws Exceptions\ForbiddenException
     * @throws Exceptions\NotFoundException
     * @throws Exceptions\UnauthorizedException
     * @throws Exceptions\ValidationException
     * @throws GuzzleException
     */
    public static function create($project_id, $attributes = [], array $options = []): User
    {
        return (new static(['project_id' => $project_id]))->_create($attributes, $options);
    }

    public function notifications(): NotificationsQuery
    {
        return new NotificationsQuery($this->project_id, $this->id);
    }
}
