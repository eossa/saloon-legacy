<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Exception;
use Saloon\Http\Request;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Tests\Fixtures\Requests\HeaderErrorRequest;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;

class SendAndRetryTest extends TestCase
{
    public function testAFailedRequestCanBeRetried()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $response = $connector->sendAndRetry(new UserRequest(), 3);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['name' => 'Teodor'], $response->json());

        $mockClient->assertSentCount(3);
    }

    public function testIfTheAttemptsAreExhaustedItWillThrowAnExceptionFromTheLastRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 500),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $hitException = false;

        try {
            $connector->sendAndRetry(new UserRequest(), 3);
        } catch (Exception $exception) {
            $this->assertInstanceOf(InternalServerErrorException::class, $exception);
            $this->assertEquals(['name' => 'Teodor'], $exception->getResponse()->json());

            $hitException = true;
        }

        $this->assertTrue($hitException);
        $mockClient->assertSentCount(3);
    }

    public function testIfTheAttemptsAreExhaustedItWillReturnTheLastResponseIfThrowingIsDisabled()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 500),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $response = $connector->sendAndRetry(new UserRequest(), 3, 0, null, false);

        $this->assertEquals(['name' => 'Teodor'], $response->json());

        $mockClient->assertSentCount(3);
    }

    public function testIfAFatalRequestExceptionHappensEvenWithThrowDisabledItWillThrowTheFatalRequestException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 500)->throwException(function ($pendingRequest) {
                return new FatalRequestException(new Exception(), $pendingRequest);
            }),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $this->expectException(FatalRequestException::class);

        $connector->sendAndRetry(new UserRequest(), 3, 0, null, false);
    }

    public function testAFailedRequestCanHaveAnIntervalBetweenEachAttempt()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $start = microtime(true);

        $connector->sendAndRetry(new UserRequest(), 3, 1000);

        // It should be a duration of 2000ms (2 seconds) because the there are two requests
        // after the first.

        $this->assertGreaterThanOrEqual(2, round(microtime(true) - $start));
    }

    public function testAFailedRequestCanHaveAnIntervalWithExponentialBackoffBetweenEachAttempt()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500), // 1,000
            MockResponse::make(['name' => 'Gareth'], 500), // 2,000
            MockResponse::make(['name' => 'Michael'], 500), // 4,000
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $start = microtime(true);

        $connector->sendAndRetry(new UserRequest(), 4, 1000, null, true, null, true);

        // It should be a duration of > 7000ms (7 seconds) because the there are four requests
        // after the first.

        $this->assertGreaterThanOrEqual(7, round(microtime(true) - $start));
    }

    public function testAnExceptionOtherThanARequestExceptionWillNotBeRetried()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $connector->middleware()->onResponse(function () {
            throw new Exception('Yee-naw!');
        });

        $hitException = false;

        try {
            $connector->sendAndRetry(new UserRequest(), 3);
        } catch (Exception $ex) {
            $this->assertEquals('Yee-naw!', $ex->getMessage());
            $hitException = true;
        }

        $this->assertTrue($hitException);

        $mockClient->assertSentCount(1);
    }

    public function testYouCanCustomiseIfTheMethodShouldRetry()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage('Internal Server Error (500) Response: {"name":"Gareth"}');

        $connector->sendAndRetry(new UserRequest(), 3, 0, function (RequestException $exception, Request $request) {
            return $exception->getResponse()->json() !== ['name' => 'Gareth'];
        });
    }

    public function testIfTheHandleRetryReturnsFalseItWillThrowAnException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $this->expectException(InternalServerErrorException::class);
        $this->expectExceptionMessage('Internal Server Error (500) Response: {"name":"Sam"}');

        $connector->sendAndRetry(new UserRequest(), 3, 0, function () {
            return false;
        });
    }

    public function testIfTheHandleRetryReturnsFalseAndThrowOptionIsDisabledItWillReturnAResponse()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $response = $connector->sendAndRetry(new UserRequest(), 5, 0, function () {
            return false;
        }, false);

        $this->assertEquals(500, $response->status());
        $this->assertEquals(['name' => 'Sam'], $response->json());
    }

    public function testIfTheHandleRetryReturnsFalseAndThrowOptionIsDisabledButAFatalRequestExceptionHappensItWillStillThrow()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500)->throwException(function ($pendingRequest) {
                return new FatalRequestException(new Exception(), $pendingRequest);
            }),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $this->expectException(FatalRequestException::class);

        $connector->sendAndRetry(new UserRequest(), 5, 0, function () {
            return false;
        }, false);
    }

    public function testYouCanModifyTheRequestInsideTheRetryHandler()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
            MockResponse::make(['name' => 'Teodor'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $index = 0;

        $response = $connector->sendAndRetry(new UserRequest(), 5, 0, function (Exception $exception, Request $request) use (&$index) {
            $index++;

            $request->headers()->add('X-Test-Index', $index);

            return true;
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['name' => 'Teodor'], $response->json());
        $this->assertEquals(2, $response->getPendingRequest()->headers()->get('X-Test-Index'));
    }

    public function testRetryAgainstALiveEndpointToTestGuzzleSender()
    {
        $requestCount = 0;

        $connector = new TestConnector();

        $connector->middleware()->onRequest(function () use (&$requestCount) {
            $requestCount++;
        });

        $request = new HeaderErrorRequest();
        $index = 0;

        $response = $connector->sendAndRetry($request, 6, 0, function (Exception $exception, Request $request) use (&$index) {
            $request->headers()->add('X-Yee-Haw', $index++);

            return true;
        });

        // Request count is five because:
        // Request 1 - no header
        // Request 2 - header but 0
        // Request 3 - header but 1
        // Request 4 - header but 2
        // Request 5 - header but 3

        $this->assertEquals(5, $requestCount);
        $this->assertEquals('Success!', $response->body());
    }

    public function testYouCanAuthenticateTheRequestInsideTheRetryHandler()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 401),
            MockResponse::make(['name' => 'Gareth'], 200),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $response = $connector->sendAndRetry(new UserRequest(), 2, 0, function (Exception $exception, Request $request) {
            $request->authenticate(new TokenAuthenticator('newToken'));

            return true;
        });

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['name' => 'Gareth'], $response->json());
        $this->assertEquals('Bearer newToken', $response->getPendingRequest()->headers()->get('Authorization'));
    }

    public function testTheResponsePipelineIsOnlyExecutedOnceWhenRetrying()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 500),
            MockResponse::make(['name' => 'Gareth'], 500),
        ]);

        $counter = 0;

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $connector->middleware()->onResponse(function () use (&$counter) {
            $counter++;
        });

        $response = $connector->sendAndRetry(new UserRequest(), 2, 0, null, false);

        $this->assertEquals(500, $response->status());
        $this->assertEquals(['name' => 'Gareth'], $response->json());

        // Counter should be 2 as we have sent to requests

        $this->assertEquals(2, $counter);
    }
}
