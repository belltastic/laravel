<?php

namespace Belltastic\Tests\Stubs;

use Illuminate\Notifications\Notification;

class SampleNotificationToBelltastic extends Notification
{
    /** @var array */
    protected $toBelltastic;

    public function __construct($toBelltastic = [])
    {
        $this->toBelltastic = $toBelltastic;
    }

    public function via($notifiable)
    {
        return ['belltastic'];
    }

    public function toBelltastic($notifiable)
    {
        return $this->toBelltastic;
    }
}
