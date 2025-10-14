<?php

namespace Saloon\Tests\Unit;

use Exception;
use Saloon\Http\Response;
use GuzzleHttp\Promise\Promise;
use Saloon\Http\PendingRequest;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Exceptions\TestResponseException;

class AsyncRequestTest extends TestCase
{
    public function testAnAsynchronousRequestWillReturnASaloonResponseOnASuccessfulRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $request = new UserRequest();
        $promise = connector()->sendAsync($request, $mockClient);

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $response = $promise->wait();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['name' => 'Sam'], $response->json());
        $this->assertEquals(200, $response->status());
    }

    public function testAnAsynchronousRequestWillThrowASaloonExceptionOnAnUnsuccessfulRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        $request = new UserRequest();
        $promise = connector()->sendAsync($request, $mockClient);

        $this->assertInstanceOf(Promise::class, $promise);

        try {
            $promise->wait();
        } catch (Exception $exception) {
            $this->assertInstanceOf(RequestException::class, $exception);

            $response = $exception->getResponse();

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(['error' => 'Server Error'], $response->json());
            $this->assertEquals(500, $response->status());
            $this->assertEquals($exception, $response->toException());
        }
    }

    public function testAnAsynchronousRequestWillThrowAnExceptionIfAConnectionErrorHappens()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Patrick'])->throwException(function (PendingRequest $pendingRequest) {
                return new TestResponseException('Unable to connect!', $pendingRequest);
            }),
        ]);

        $request = new UserRequest();
        $promise = connector()->sendAsync($request, $mockClient);

        try {
            $promise->wait();
        } catch (Exception $exception) {
            $this->assertInstanceOf(TestResponseException::class, $exception);
            $this->assertEquals('Unable to connect!', $exception->getMessage());
            $this->assertInstanceOf(PendingRequest::class, $exception->getPendingRequest());
        }
    }

    public function testIfYouChainAnAsynchronousRequestYouCanHaveAResponse()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam'], 200),
        ]);

        $request = new UserRequest();
        $promise = connector()->sendAsync($request, $mockClient);

        $testCase = $this;
        $promise->then(
            function (Response $response) use ($testCase) {
                $testCase->assertInstanceOf(Response::class, $response);
            }
        );

        $promise->wait();
    }

    public function testIfYouChainAnErroneousAsynchronousRequestTheErrorCanBeCaughtInTheRejectionHandler()
    {
        $mockClient = new MockClient([
            MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        $request = new UserRequest();
        $promise = connector()->sendAsync($request, $mockClient);

        $testCase = $this;
        $promise = $promise->then(
            null,
            function (RequestException $exception) use ($testCase) {
                $response = $exception->getResponse();

                $testCase->assertInstanceOf(Response::class, $response);
                $testCase->assertEquals(500, $response->status());
                $testCase->assertSame($exception, $response->getRequestException());
            }
        );

        $promise->wait(false);
    }

    public function testIfAConnectionExceptionHappensItWillBeProvidedInTheRejectionHandler()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Patrick'])->throwException(function (PendingRequest $pendingRequest) {
                return new TestResponseException('Unable to connect!', $pendingRequest);
            }),
        ]);

        $request = new UserRequest();
        $promise = connector()->sendAsync($request, $mockClient);

        $testCase = $this;
        $promise = $promise->then(
            null,
            function ($exception) use ($testCase) {
                $testCase->assertEquals('Unable to connect!', $exception->getMessage());
            }
        );

        $promise->wait();
    }
}
