<?php

namespace Belltastic;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @package Belltastic
 *
 * @property-read int $id
 * @property string $name
 * @property bool $default
 * @property bool $hmac_required
 * @property-read int $team_id
 * @property-read Carbon $created_at
 * @property-read Carbon $deleted_at
 *
 * @method static Collection|Project[] all(array $options = []) Get a collection of all projects
 * @method static Project|null find(int $id) Get a single project
 * @method static Project create(array $attributes, array $options = []) Create a new project
 * @method $this update(array $attributes = [], array $options = []) Update the project with new values
 * @method void save(array $options = []) Save the project's changes
 * @method void delete(array $options = []) Archive (soft-delete) the project.
 * @method void forceDelete(array $options = []) Delete the project completely. This will also remove its related users and notifications. There is no way to restore this data.
 */
class Project extends ApiResource
{
    use ApiOperations\All;
    use ApiOperations\Find;
    use ApiOperations\Create;
    use ApiOperations\Update;
    use ApiOperations\Delete;

    protected $paginated = false;

    protected function listUrl(): string
    {
        return 'v1/projects';
    }

    protected function instanceUrl(): string
    {
        return "v1/project/$this->id";
    }

    public function users(): UsersQuery
    {
        return new UsersQuery($this->id);
    }
}
