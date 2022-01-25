<?php

namespace Belltastic;

use Belltastic\Channels\BelltasticChannel;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Notification;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BelltasticServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('belltastic')
            ->hasConfigFile();
    }

    public function packageRegistered()
    {
        $this->app->bind('belltastic-api-client', function () {
            return new Client();
        });

        Notification::extend('belltastic', function () {
            return new BelltasticChannel();
        });
    }

    public function packageBooted()
    {
        Blade::directive('belltasticComponent', function () {
            $user_id = auth()->id();
            $project_id = config('belltastic.default_project', null);
            $api_key = config("belltastic.projects.$project_id.api_key");
            $userData = '';
            if ($user = auth()->user()) {
                $userData = "user='".json_encode([
                    'name' => $user->name,
                    'email' => $user->email,
                ])."'";
            }

            return <<<COMP
<belltastic-notifications
    api-key="$api_key"
    project-id="$project_id"
    user-id="$user_id"
    $userData
></belltastic-notifications>
<script src="https://belltastic.com/component/belltastic.es.js" defer></script>
COMP;
        });
    }
}
