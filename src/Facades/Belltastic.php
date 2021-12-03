<?php

namespace Belltastic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Belltastic\Belltastic\Belltastic
 */
class Belltastic extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel';
    }
}
