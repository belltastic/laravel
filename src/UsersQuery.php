<?php

namespace Belltastic;

use Illuminate\Support\LazyCollection;

/**
 * @package Belltastic
 *
 * @method LazyCollection all(array $options = []) Get a list of users in this project.
 * @method User find($id, array $options = []) Get a single user from this project.
 * @method User create(array $attributes = [], array $options = []) Create a new user in this project.
 */
class UsersQuery extends Query
{
    protected $model = User::class;
}
