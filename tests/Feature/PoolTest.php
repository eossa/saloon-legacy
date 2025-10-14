<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Tests\Fixtures\Connectors\InvalidConnectionConnector;

class PoolTest extends TestCase
{
    public function testYouCanCreateAPoolOnAConnector()
    {
        $connector = new TestConnector();
        $successCount = 0;
        $errorCount = 0;

        $pool = $connector->pool([
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new ErrorRequest(),
        ]);

        $pool->setConcurrency(6);

        $pool->withResponseHandler(function (Response $response) use (&$successCount) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals([
                'name' => 'Sammyjo20',
                'actual_name' => 'Sam',
                'twitter' => '@carre_sam',
            ], $response->json());

            $successCount++;
        });

        $pool->withExceptionHandler(function (RequestException $exception) use (&$errorCount) {
            $response = $exception->getResponse();

            $this->assertInstanceOf(Response::class, $response);

            $errorCount++;
        });

        $promise = $pool->send();

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $promise->wait();

        $this->assertEquals(5, $successCount);
        $this->assertEquals(1, $errorCount);
    }

    public function testIfAPoolHasARequestThatCannotConnectItWillBeCaughtInTheHandleExceptionCallback()
    {
        $connector = new InvalidConnectionConnector();
        $count = 0;

        $pool = $connector->pool([
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
        ]);

        $pool->setConcurrency(5);

        $pool->withExceptionHandler(function (FatalRequestException $ex) use (&$count) {
            $this->assertInstanceOf(FatalRequestException::class, $ex);
            $this->assertInstanceOf(ConnectException::class, $ex->getPrevious());
            $this->assertInstanceOf(PendingRequest::class, $ex->getPendingRequest());

            $count++;
        });

        $promise = $pool->send();

        $promise->wait();

        $this->assertEquals(5, $count);
    }

    public function testYouCanUsePoolWithAMockClientAddedAndItWontSendRealRequests()
    {
        $mockResponses = [
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Charlotte']),
            MockResponse::make(['name' => 'Mantas']),
            MockResponse::make(['name' => 'Emily']),
            MockResponse::make(['name' => 'Error'], 500),
        ];

        $mockClient = new MockClient($mockResponses);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);
        $successCount = 0;
        $errorCount = 0;

        $pool = $connector->pool([
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new UserRequest(),
            new ErrorRequest(),
        ]);

        $pool->setConcurrency(6);

        $pool->withResponseHandler(function (Response $response) use (&$successCount, $mockResponses) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals($mockResponses[$successCount]->body()->all(), $response->json());

            $successCount++;
        });

        $pool->withExceptionHandler(function (RequestException $exception) use (&$errorCount) {
            $response = $exception->getResponse();

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(['name' => 'Error'], $response->json());

            $errorCount++;
        });

        $promise = $pool->send();

        $promise->wait();

        $this->assertEquals(4, $successCount);
        $this->assertEquals(1, $errorCount);
    }
}
