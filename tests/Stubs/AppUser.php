<?php

namespace Belltastic\Tests\Stubs;

use Illuminate\Notifications\Notifiable;

class AppUser
{
    use Notifiable;

    /** @var \Belltastic\User|array */
    protected $routesTo;

    /**
     * @param \Belltastic\User|array $routesTo
     */
    public function __construct($routesTo = [])
    {
        $this->routesTo = $routesTo;
    }

    /**
     * Get a Belltastic User to route this notification to.
     *
     * @return \Belltastic\User|array
     */
    public function routeNotificationForBelltastic()
    {
        return $this->routesTo;
    }
}
