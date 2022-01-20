<p align="center">
    <img width="260" src="https://belltastic.com/images/BelltasticDark.svg" />
</p>

# Belltastic for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/belltastic/laravel.svg?style=flat-square)](https://packagist.org/packages/belltastic/laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/belltastic/laravel/run-tests?label=tests)](https://github.com/belltastic/laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/belltastic/laravel/Check%20&%20fix%20styling?label=code%20style)](https://github.com/belltastic/laravel/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)

**Belltastic** is a pre-built web component to display in-app notifications. Copy the code snippet to your website, plug in a few props, and your users will have a fully-functional, real-time notifications drawer.

<p align="center">
<img width="677" alt="Belltastic web component" src="https://user-images.githubusercontent.com/8697942/150183408-8058365c-bb2c-4025-8dd4-7003caa2c016.png">
</p>

### About this package

This [Composer](https://packagist.org/packages/belltastic/laravel) package will help you easily send [Belltastic](https://belltastic.com) notifications from your [Laravel](https://laravel.com/) app to your users. Check out the links above to learn how to set up the component before you begin sending notifications.

## Laravel Version Compatibility

This package requires at least **PHP 7.3** and **Laravel 8**.

## Links

- [Sign up for a **free account**](https://belltastic.com)
- [Learn how to **set up the component**](https://belltastic.com/docs/component/#installing-the-component)
- [Learn how to **customise the component**](https://belltastic.com/docs/component/styling.html)
- [Learn how to **send notifications**](https://belltastic.com/docs/component/sending-notifications.html)

## Installation

You can install the package via composer:

```bash
composer require belltastic/laravel
```

And then publish the config file with:
```bash
php artisan vendor:publish --tag="belltastic-config"
```

For reference, here are the contents of the published config file:

```php
<?php

return [
    'base_uri' => env('BELLTASTIC_API_URL', 'https://belltastic.com/api/v1/'),

    /**
     * Owner API key (starts with user_...) that you can retrieve from here:
     * @link https://belltastic.com/user/api-tokens
     *
     * This will be the token used by default, unless otherwise provided
     * in the $options parameter for Belltastic models.
     */
    'api_key' => env('BELLTASTIC_API_KEY'),

    /**
     * Verbose exceptions will contain more information about the request in the exception
     * message. The request URL and the beginning of an API token used (user_zx312****)
     * will be added for easier debugging of configuration.
     */
    'verbose_exceptions' => true,

    /**
     * A list of Belltastic projects that this app interacts with.
     *
     * By default, and in most cases, you only need one project and its
     * secret in order to generate valid HMAC authorization tokens.
     */
    'projects' => [
        // this is a configuration for a Belltastic project with ID of 1
        env('BELLTASTIC_PROJECT_ID', '1') => [
            // The secret is used to calculate User HMAC values with $user->hmac() method.
            'secret' => env('BELLTASTIC_PROJECT_SECRET', ''),
        ],

        // add more projects if needed
    ]
];
```

## Sending Notifications

Best and easiest way to send notifications to the Belltastic component is via Laravel Notifications.

### 1. Get the API key

First of all you'll need to get an API token, so you can securely communicate with our servers and access your data. You can get the token from the [API Tokens](https://belltastic.com/user/api-tokens) page.
The token will have a `user_` prefix.

Put it in your `.env` file like so:
```dotenv
BELLTASTIC_API_KEY="user_xxxxxxxxxxxxxxxxxxx"
```

### 2a (using Laravel Notifications) Set up your User model

_**NOTE**: there's also another way to send the notifications without utilising Laravel's Notifications. Read more about it in the [2b section](#2b-send-using-the-belltasticnotification-model)._

In order for Laravel to know where to send the notifications, you must implement the
`routeNotificationForBelltastic()` method on your User model:

```php
class User extends Authenticatable
{
    /**
     * Get a Belltastic User to route this notification to.
     *
     * @return \Belltastic\User|array
     */
    public function routeNotificationForBelltastic()
    {
        return new \Belltastic\User([
            'id' => $this->id,
            'project_id' => 1,
        ]);

        // Alternatively, you can return a plain array
        return [
            'id' => $this->id,
            'project_id' => 1,
        ];
    }
}
```

### 3a. (using Laravel Notifications) Sending notifications

If you're already utilising [Laravel Notifications](https://laravel.com/docs/8.x/notifications) in your project, changing them to send to Belltastic is as easy
as adding the `'belltastic'` channel.
```php
class SampleNotification extends Notification
{
    public function via($notifiable)
    {
        return ['belltastic'];
    }
```

In order to define the contents of the notification, you can either implement the `toArray($notifiable)` or the `toBelltastic($notifiable)` (will take priority, if exists) methods:

```php
class SampleNotification extends Notification
{
    public function via($notifiable)
    {
        return ['belltastic'];
    }

    /**
     * Get the contents of the notification
     * 
     * @param  $notifiable
     * @return \Belltastic\Notification|array
     */
    public function toBelltastic($notifiable)
    {
        return [
            'title'      => 'Notification title',
            'body'       => 'And a longer body that explains more about the event.',
            'action_url' => 'https://your-cta-link.test',
            'category'   => 'system',       // or any other string that defines this notification's category
            'icon'       => 'https://example.com/icon.png',
        ];
        
        // Alternatively, you can also return an instance of \Belltastic\Notification
        // containing the data:
        return new \Belltastic\Notification([
            'title' => 'Notification title',
            // and other properties...
        ]);
    }
    
    // As a fallback, this method will be called if
    // the `toBelltastic($notifiable)` is not defined.
    public function toArray($notifiable)
    {
        return [
            'title' => 'Notification title',
            // and other properties...
        ];
    }
}
```

Once you have set up both the User model and one or more Laravel Notifications, then sending them is as easy as calling `$user->notify()`:
```php
$user->notify(new SampleNotification());
```

### 2b. Send using the \Belltastic\Notification model

If you don't want to use Laravel Notifications, you can simply call `Belltastic\Notification::create()` to send a new notification from any place in your code:
```php
// creates and sends a new notification
\Belltastic\Notification::create($project_id, $user_id, [
    // [required|string]
    // title of the notification, displayed in bolder text
    'title' => 'New comment on your post "Laravel Basics"',
    
    // [nullable|string]
    // body of the notification, smaller text
    'body' => 'Joe Belltastic has left a comment on your post. Click here to see more.',
    
    // [nullable|string]
    // link to an icon/avatar to display next to notification
    'icon' => null,
    
    // [nullable|string]
    // link to visit when the user clicks a notification
    'action_url' => 'https://example-blog.com/posts/1234?comments',
    
    // [nullable|string]
    // category of the notification, used for segments in the future
    'category' => 'comments',
]);
```

You can read more about it in the [Interacting with Notifications](#Interacting-with-Notifications) section below.

## Interacting with account data

Sometimes you'll need a more fine-grained control over your data at Belltastic. For this, you can utilise common operations on the Belltastic models that you can interact with in a familiar manner.

By default, any operations will use the API key provided in the `belltastic.api_key` configuration value. If you would like to use a different API key, you can set it like so:

```php
// Set the API key globally, so you can then interact with all objects as described below.
\Belltastic\Belltastic::setApiKey('user_YVBpHfvUh...md5Iq');

// Or provide the API key on each request within the last $options param.
\Belltastic\Project::find(1, ['api_key' => 'user_YVBpHfvUh...md5Iq']);
\Belltastic\Project::all(['api_key' => 'user_YVBpHfvUh...md5Iq']);
// etc...
```

### Interacting with Projects
```php
// Returns a Collection of all projects accessible with your API key
\Belltastic\Project::all();

// Returns a single project
\Belltastic\Project::find($id);

// Creates a new project
\Belltastic\Project::create([
    'team_id' => 1,         // REQUIRED, we must know which Belltastic team this project belongs to
    'name' => 'Test project',
]);

// Update a project, either update() or save()
$project->update(['name' => 'New name']);
$project->name = 'New name';
$project->save();

// Archiving/Deleting a project
$project->archive();    // archives (soft-deletes). Can be restored later.
$project->destroy();    // permanently delete. No way to restore it.

// Project has many users relation
$project->users()->all();
$project->users()->find($user_id);
$project->users()->create([
    'id' => $user_id,       // REQUIRED, must match your system's user ID
    'name' => 'Test User'
]);
```

### Interacting with Users

```php
// returns a LazyCollection of all users for the given project ID
\Belltastic\User::all($project_id);

// returns a single user from the provided project ID
\Belltastic\User::find($project_id, $id);

// Notice how you must provide the ID yourself when creating a user,
// because it should match the user ID in your system:
\Belltastic\User::create($project_id, [
    'id' => $user_id,       // REQUIRED, must match your system's user ID
    'name' => 'Test User'
]);

// Updating a user instance, use either update() or save()
$user->update(['name' => 'New user name']);
$user->name = 'New user name';
$user->save();

// Archiving/Deleting a user
$user->archive();   // archives (soft-deletes). Can be restored later.
$user->destroy();   // permanently delete. No way to restore it.

// Return the HMAC authorization string for this user.
// Read more about HMAC here: https://belltastic.com/docs/component/hmac.html
$hmac_value = $user->hmac();
// Or, preferrably without loading a user instance via HTTP call, for speed:
$hmac_value = \Belltastic\User::hmac($project_id, $user_id);

// User has many notifications relation:
$user->notifications()->all();
$user->notifications()->find($id);
$user->notifications()->create(['title' => 'Here\'s a notification']);
```

### Interacting with Notifications

**NOTE:** One major difference from other entities is that Notifications cannot be updated after being created, so make sure your `title`, `body` and other attributes are correct when creating it.

The only state changes possible after the notification has already been created - marking it as seen, read, unread and deleting it.

```php
// returns a LazyCollection of all notifications from the given project and user
\Belltastic\Notification::all($project_id, $user_id);

// returns a single notification from the given project and user
\Belltastic\Notification::find($project_id, $user_id, $id);

// creates a new notification
\Belltastic\Notification::create($project_id, $user_id, [
    // [required|string]
    // title of the notification, displayed in bolder text: 
    'title' => 'New comment on your post "Laravel Basics"',
    
    // [nullable|string]
    // body of the notification, smaller text:
    'body' => 'Joe Belltastic has left a comment on your post. Click here to see more.',
    
    // [nullable|string]
    // link to an icon/avatar to display next to notification:
    'icon' => null,
    
    // [nullable|string]
    // link to visit when the user clicks a notification:
    'action_url' => 'https://example-blog.com/posts/1234?comments',
    
    // [nullable|string]
    // category of the notification, used for segments in the future
    'category' => 'comments',
]);

// notification state changes
$notification->markAsSeen();
$notification->markAsRead();
$notification->markAsUnread();

// Archive/Delete a notification
$notification->archive();   // archives (soft-deletes). Can be restored later.
$notification->destroy();   // permanently delete. No way to restore it.
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Belltastic Team](https://belltastic.com)
- [Arunas Skirius](https://github.com/arukompas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
