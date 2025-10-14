<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use PHPUnit_Framework_ExpectationFailedException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use PHPUnit\Framework\TestCase;

class MockClientAssertionsTest extends TestCase
{
    public function testAssertSentWorksWithARequest()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ]);

        connector()->send(new UserRequest(), $mockClient);

        $mockClient->assertSent(UserRequest::class);
    }

    public function testAssertSentWorksWithAClosure()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
            ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        $originalRequest = new UserRequest();
        $originalResponse = connector()->send($originalRequest, $mockClient);

        $mockClient->assertSent(function ($request, $response) use ($originalRequest, $originalResponse) {
            $this->assertInstanceOf(Request::class, $request);
            $this->assertInstanceOf(Response::class, $response);

            $this->assertSame($originalRequest, $request);
            $this->assertSame($originalResponse, $response);

            return true;
        });

        $newRequest = new ErrorRequest();
        $newResponse = connector()->send($newRequest, $mockClient);

        $mockClient->assertSent(function ($request, $response) use ($newRequest, $newResponse) {
            $this->assertInstanceOf(Request::class, $request);
            $this->assertInstanceOf(Response::class, $response);

            $this->assertSame($newRequest, $request);
            $this->assertSame($newResponse, $response);

            return true;
        });
    }

    public function testAssertSentWorksWithAUrl()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ]);

        connector()->send(new UserRequest(), $mockClient);

        $mockClient->assertSent('saloon.dev/*');
        $mockClient->assertSent('/user');
        $mockClient->assertSent('api/user');
    }

    public function testAssertNotSentWorksWithARequest()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
            ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        connector()->send(new ErrorRequest(), $mockClient);

        $mockClient->assertNotSent(UserRequest::class);
    }

    public function testAssertNotSentWorksWithAClosure()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
            ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        $originalRequest = new ErrorRequest();
        $originalResponse = connector()->send($originalRequest, $mockClient);

        $mockClient->assertNotSent(function ($request) {
            return $request instanceof UserRequest;
        });
    }

    public function testAssertNotSentWorksWithAUrl()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ]);

        connector()->send(new UserRequest(), $mockClient);

        $mockClient->assertNotSent('google.com/*');
        $mockClient->assertNotSent('/error');
    }

    public function testAssertSentJsonWorksProperly()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ]);

        connector()->send(new UserRequest(), $mockClient);

        $mockClient->assertSentJson(UserRequest::class, [
            'name' => 'Sam',
        ]);
    }

    public function testAssertSentJsonWorksWithMultipleRequestsInHistory()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor']),
            MockResponse::make(['name' => 'Marcel']),
        ]);

        $connector = new TestConnector();

        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);

        $mockClient->assertSentJson(UserRequest::class, [
            'name' => 'Sam',
        ]);

        $mockClient->assertSentJson(UserRequest::class, [
            'name' => 'Taylor',
        ]);

        $mockClient->assertSentJson(UserRequest::class, [
            'name' => 'Marcel',
        ]);
    }

    public function testAssertNothingSentWorksProperly()
    {
        $mockClient = new MockClient([
            UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ]);

        $mockClient->assertNothingSent();
    }

    public function testAssertSentCountWorksProperly()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor']),
            MockResponse::make(['name' => 'Marcel']),
        ]);

        $connector = new TestConnector();

        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);

        $mockClient->assertSentCount(3);
    }

    public function testCanAssertCountOfRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor']),
            MockResponse::make(['name' => 'Marcel']),
            MockResponse::make(['message' => 'Error'], 500),
        ]);

        $connector = new TestConnector();

        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new ErrorRequest(), $mockClient);

        $mockClient->assertSentCount(3, UserRequest::class);
        $mockClient->assertSentCount(1, ErrorRequest::class);
    }

    public function testAssertSentWithAClosureWorksWithMoreThanOneRequestInTheHistory()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor'], 201),
            MockResponse::make(['name' => 'Marcel'], 204),
        ]);

        $connector = new TestConnector();

        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);

        $mockClient->assertSent(function ($request, $response) {
            return $response->json() === ['name' => 'Sam'] && $response->status() === 200;
        });

        $mockClient->assertSent(function ($request, $response) {
            return $response->json() === ['name' => 'Taylor'] && $response->status() === 201;
        });

        $mockClient->assertSent(function ($request, $response) {
            return $response->json() === ['name' => 'Marcel'] && $response->status() === 204;
        });
    }

    public function testItCanAssertRequestsAreSentInASpecificOrder()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor'], 201),
            MockResponse::make(['name' => 'Marcel'], 204),
        ]);

        $connector = new TestConnector();

        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);
        $connector->send(new UserRequest(), $mockClient);

        $mockClient->assertSentInOrder([
            UserRequest::class,
            function (UserRequest $request, Response $response) {
                return $response->json() === ['name' => 'Taylor'];
            },
            '/user',
        ]);
    }

    public function testItCanAssertRequestsAreSentInASpecificOrderFailure()
    {
        $this->expectException(PHPUnit_Framework_ExpectationFailedException::class);

        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor'], 201),
            MockResponse::make(['name' => 'Marcel'], 204),
        ]);

        $connector = new TestConnector();

        $connector->send(new UserRequest(2), $mockClient);
        $connector->send(new UserRequest(1), $mockClient);
        $connector->send(new UserRequest(), $mockClient);

        $mockClient->assertSentInOrder([
            UserRequest::class,
            function (UserRequest $request) {
                return $request->userId === 2;
            },
            '/user',
        ]);
    }
}
