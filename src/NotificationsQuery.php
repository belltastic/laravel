<?php

namespace Belltastic;

use Illuminate\Support\LazyCollection;

/**
 * @package Belltastic
 *
 * @method LazyCollection all(array $options = []) Get a list of notifications on this user.
 * @method Notification find(string $id, array $options = []) Get a single notification from this user.
 * @method Notification create(array $attributes = [], array $options = []) Create a new notification for this user.
 */
class NotificationsQuery extends Query
{
    protected $model = Notification::class;
}
