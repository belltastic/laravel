<?php

namespace Belltastic\Tests\Stubs;

use Illuminate\Notifications\Notification;

class SampleNotificationToArray extends Notification
{
    /** @var array */
    protected $toArray;

    public function __construct($toArray = [])
    {
        $this->toArray = $toArray;
    }

    public function via($notifiable)
    {
        return ['belltastic'];
    }

    public function toArray($notifiable)
    {
        return $this->toArray;
    }
}
