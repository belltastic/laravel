<?php

namespace Belltastic\Channels;

use Belltastic\User;
use Illuminate\Notifications\Notification;
use RuntimeException;

class BelltasticChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return \Belltastic\Notification
     */
    public function send($notifiable, Notification $notification)
    {
        $belltasticUser = $notifiable->routeNotificationFor('belltastic', $notification);

        if (is_array($belltasticUser)) {
            $belltasticUser = new User($belltasticUser);
        }

        return $belltasticUser->notifications()->create(
            $this->getData($notifiable, $notification)
        );
    }

    /**
     * Get the data for the notification.
     *
     * @param  mixed  $notifiable
     * @param Notification $notification
     * @return array
     *
     * @throws RuntimeException
     */
    protected function getData($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toBelltastic')) {
            return is_array($data = $notification->toBelltastic($notifiable))
                ? $data : $data->toFlatArray();
        }

        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }

        throw new RuntimeException('Notification is missing toBelltastic / toArray method.');
    }
}
