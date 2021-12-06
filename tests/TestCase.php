<?php

namespace Belltastic\Tests;

use Belltastic\BelltasticServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /** @var MockHandler */
    protected $clientMock;

    protected $requestHistory = [];

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

    public function mockApiClient(): void
    {
        if (is_null($this->clientMock)) {
            $this->clientMock = new MockHandler([]);

            $this->app->bind('belltastic-api-client', function () {
                $handlerStack = HandlerStack::create($this->clientMock);
                $handlerStack->push(Middleware::history($this->requestHistory));

                return new Client(['handler' => $handlerStack]);
            });
        }
    }

    public function resetApiClient(): void
    {
        $this->app->bind('belltastic-api-client', function () {
            return new Client();
        });
        $this->requestHistory = [];
        $this->clientMock = null;
    }

    public function queueMockResponse($statusCode, $data = [], $headers = [])
    {
        $this->mockApiClient();
        $this->clientMock->append(new Response(
            $statusCode,
            array_merge([
                'Content-Type' => 'application/json',
            ], $headers),
            json_encode($data)
        ));
    }

    public function getRequests(): array
    {
        return array_map(function ($transaction) {
            return $transaction['request'];
        }, $this->requestHistory);
    }

    public function getFirstRequest(): ?Request
    {
        return $this->getRequests()[0];
    }

    public function getLastRequest(): ?Request
    {
        $requests = $this->getRequests();

        return $requests[count($requests) - 1];
    }

    public function assertRequestCount(int $expectedCount)
    {
        $this->assertCount($expectedCount, $this->getRequests());
    }
}
