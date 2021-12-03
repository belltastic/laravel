<?php

namespace Belltastic\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Belltastic\BelltasticServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Belltastic\\Database\\Factories\\'.class_basename($modelName).'Factory';
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            BelltasticServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel_table.php.stub';
        $migration->up();
        */
    }
}
