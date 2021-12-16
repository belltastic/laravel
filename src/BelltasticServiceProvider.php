<?php

namespace Belltastic;

use Belltastic\Channels\BelltasticChannel;
use GuzzleHttp\Client;
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
}
