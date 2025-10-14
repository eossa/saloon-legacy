<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

class AsyncRequestTest extends TestCase
{
    public function testAsynchronousRequestCanBeMadeSuccessfully()
    {
        $promise = TestConnector::make()->sendAsync(new UserRequest());

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $response = $promise->wait();

        $this->assertInstanceOf(Response::class, $response);

        $data = $response->json();

        $this->assertTrue($response->getPendingRequest()->isAsynchronous());
        $this->assertFalse($response->isMocked());
        $this->assertEquals(200, $response->status());

        $this->assertEquals([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ], $data);
    }

    public function testAsynchronousRequestCanHandleAnExceptionProperly()
    {
        $promise = TestConnector::make()->sendAsync(new ErrorRequest());

        $this->expectException(RequestException::class);

        $promise->wait();
    }

    public function testAsynchronousResponseWillStillBePassedThroughResponseMiddleware()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $request = new UserRequest();

        $passed = false;

        $request->middleware()->onResponse(function (Response $response) use (&$passed) {
            $passed = true;
        });

        $connector = new TestConnector();

        $promise = $connector->sendAsync($request, $mockClient);
        $response = $promise->wait();

        $this->assertTrue($passed);
    }

    public function testAsynchronousRequestWillReturnACustomResponse()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar']),
        ]);

        $connector = new TestConnector();
        $request = new UserRequestWithCustomResponse();

        $promise = $connector->sendAsync($request, $mockClient);

        $response = $promise->wait();

        $this->assertInstanceOf(UserResponse::class, $response);
        $this->assertInstanceOf(UserData::class, $response->customCastMethod());
        $this->assertEquals('bar', $response->foo());
    }

    public function testMiddlewareIsOnlyExecutedWhenAnAsynchronousRequestIsSent()
    {
        $mockClient = new MockClient([
            MockResponse::make(['foo' => 'bar']),
        ]);

        $request = new UserRequest();
        $request->withMockClient($mockClient);
        $sent = false;

        $request->middleware()->onRequest(function () use (&$sent) {
            $sent = true;
        });

        $promise = TestConnector::make()->sendAsync($request);

        $this->assertFalse($sent);

        $promise->wait();

        $this->assertTrue($sent);
    }
}
