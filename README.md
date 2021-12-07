# Belltastic Notifications for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/belltastic/laravel.svg?style=flat-square)](https://packagist.org/packages/belltastic/laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/belltastic/laravel/run-tests?label=tests)](https://github.com/belltastic/laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/belltastic/laravel/Check%20&%20fix%20styling?label=code%20style)](https://github.com/belltastic/laravel/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/belltastic/laravel.svg?style=flat-square)](https://packagist.org/packages/belltastic/laravel)

This package will help you easily send Belltastic notifications to your users. Learn more about Belltastic on [https://belltastic.com](https://belltastic.com)

## Installation

You can install the package via composer:

```bash
composer require belltastic/laravel
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="belltastic-config"
```

These are the contents of the published config file:

```php
return [
    'base_uri' => 'https://belltastic.com/api/',

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

## Usage

### Interacting with Projects
```php
// Returns a Collection of all projects accessible with your API key
\Belltastic\Project::all();

// Returns a single project
\Belltastic\Project::find($id);

// Creates a new project
\Belltastic\Project::create(['name' => 'Test project']);

// Update a project, either update() or save()
$project->update(['name' => 'New name']);
$project->name = 'New name';
$project->save();

// Archiving/Deleting a project
$project->delete();         // archives (soft-deletes). Can be restored later.
$project->forceDelete();    // permanently delete. No way to restore it.

// Project has many users relation
$project->users()->all();
$project->users()->find($user_id);
$project->users()->create(['id' => $user_id, 'name' => 'Test User']);
```

### Interacting with Users

```php
// returns a LazyCollection of all users for the given project ID
\Belltastic\User::all($project_id);

// returns a single user from the provided project ID
\Belltastic\User::find($project_id, $id);

// Notice how you must provide the ID yourself when creating a user,
// because it should match the user ID in your system:
\Belltastic\User::create($project_id, ['id' => $user_id, 'name' => 'Test User']);

// Updating a user instance, use either update() or save()
$user->update(['name' => 'New user name']);
$user->name = 'New user name';
$user->save();

// Archiving/Deleting a user
$user->delete();        // archives (soft-deletes). Can be restored later.
$user->forceDelete();   // permanently delete. No way to restore it.

// Return the HMAC authorization string for this user.
// Read more about HMAC here: https://belltastic.com/docs/component/hmac.html
$hmac_value = $user->hmac();

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
$notification->delete();        // archives (soft-deletes). Can be restored later.
$notification->forceDelete();   // permanently delete. No way to restore it.
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
