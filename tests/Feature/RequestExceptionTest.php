<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Exception;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ServerException;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Tests\Fixtures\Requests\BadResponseRequest;
use Saloon\Tests\Fixtures\Requests\NotFoundFailedRequest;
use Saloon\Tests\Fixtures\Connectors\BadResponseConnector;
use Saloon\Tests\Fixtures\Exceptions\CustomRequestException;
use Saloon\Tests\Fixtures\Requests\CustomFailHandlerRequest;
use Saloon\Tests\Fixtures\Connectors\CustomExceptionConnector;
use Saloon\Tests\Fixtures\Requests\CustomExceptionUserRequest;
use Saloon\Tests\Fixtures\Exceptions\ConnectorRequestException;
use Saloon\Tests\Fixtures\Connectors\CustomFailHandlerConnector;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\ServerException as SaloonServerException;

class RequestExceptionTest extends TestCase
{
    public function testYouCanUseTheToExceptionMethodToGetTheDefaultRequestExceptionExceptionWithGuzzleSender()
    {
        $response = TestConnector::make()->send(new ErrorRequest());

        $this->assertInstanceOf(Response::class, $response);

        $exception = $response->toException();

        $this->assertInstanceOf(InternalServerErrorException::class, $exception);
        $this->assertInstanceOf(SaloonServerException::class, $exception);
        $this->assertEquals('Internal Server Error (500) Response: ' . $response->body(), $exception->getMessage());
        $this->assertInstanceOf(ServerException::class, $exception->getPrevious());

        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        $response->throwException();
    }

    public function testYouCanUseTheToExceptionMethodToGetTheDefaultRequestExceptionException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Server Error'], 500),
        ]);

        $response = TestConnector::make()->send(new UserRequest(), $mockClient);

        $this->assertInstanceOf(Response::class, $response);

        $exception = $response->toException();

        $this->assertInstanceOf(InternalServerErrorException::class, $exception);
        $this->assertInstanceOf(SaloonServerException::class, $exception);
        $this->assertEquals('Internal Server Error (500) Response: ' . $response->body(), $exception->getMessage());

        // Previous is null with the SimulatedSender

        $this->assertEquals(null, $exception->getPrevious());

        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        $response->throwException();
    }

    public function testItThrowsExceptionsProperlyWithPromisesWithGuzzleSender()
    {
        $promise = TestConnector::make()->sendAsync(new ErrorRequest());

        $correctInstance = false;
        $caughtException = null;

        $promise->otherwise(function (Exception $exception) use (&$correctInstance) {
            if ($exception instanceof RequestException) {
                $correctInstance = true;
            }
        });

        try {
            $promise->wait();
        } catch (Exception $exception) {
            $caughtException = $exception;
        }

        $this->assertNotNull($caughtException);
        $this->assertTrue($correctInstance);
        $this->assertInstanceOf(RequestException::class, $caughtException);
        $this->assertInstanceOf(Response::class, $caughtException->getResponse());
        $this->assertEquals('Internal Server Error (500) Response: ' . $caughtException->getResponse()->body(), $caughtException->getMessage());
        $this->assertInstanceOf(ServerException::class, $caughtException->getPrevious());
    }

    public function testItThrowsExceptionsProperlyWithPromises()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Bad Request'], 422),
        ]);

        $promise = TestConnector::make()->sendAsync(new ErrorRequest(), $mockClient);

        $caughtException = null;

        try {
            $promise->wait();
        } catch (Exception $exception) {
            $caughtException = $exception;
        }

        $this->assertNotNull($caughtException);
        $this->assertInstanceOf(ClientException::class, $caughtException);
        $this->assertInstanceOf(Response::class, $caughtException->getResponse());
        $this->assertEquals('Unprocessable Entity (422) Response: ' . $caughtException->getResponse()->body(), $caughtException->getMessage());
        $this->assertNull($caughtException->getPrevious());
    }

    public function testYouCanCustomiseTheExceptionHandlerOnAConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Server Error'], 500),
        ]);

        $response = CustomExceptionConnector::make()->send(new UserRequest(), $mockClient);
        $exception = $response->toException();

        $this->assertInstanceOf(ConnectorRequestException::class, $exception);
        $this->assertEquals('Oh yee-naw.', $exception->getMessage());
    }

    public function testYouCanCustomiseTheExceptionHandlerOnARequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Server Error'], 500),
        ]);

        $response = TestConnector::make()->send(new CustomExceptionUserRequest(), $mockClient);
        $exception = $response->toException();

        $this->assertInstanceOf(CustomRequestException::class, $exception);
        $this->assertEquals('Oh yee-naw.', $exception->getMessage());
    }

    public function testTheRequestExceptionHandlerWillAlwaysTakePriority()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Server Error'], 500),
        ]);

        $response = CustomExceptionConnector::make()->send(new CustomExceptionUserRequest(), $mockClient);
        $exception = $response->toException();

        $this->assertInstanceOf(CustomRequestException::class, $exception);
        $this->assertEquals('Oh yee-naw.', $exception->getMessage());
    }

    public function testYouCanCustomiseIfSaloonShouldThrowAnExceptionOnAConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Success']),
            MockResponse::make(['message' => 'Error: Invalid Cowboy Hat']),
        ]);

        $responseA = BadResponseConnector::make()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->shouldThrowRequestException());
        $this->assertNull($responseA->toException());

        $responseB = BadResponseConnector::make()->send(new UserRequest(), $mockClient);
        $this->assertTrue($responseB->shouldThrowRequestException());
        $exceptionB = $responseB->toException();

        $this->assertInstanceOf(RequestException::class, $exceptionB);
        $this->assertInstanceOf(PendingRequest::class, $exceptionB->getPendingRequest());
        $this->assertInstanceOf(Response::class, $exceptionB->getResponse());
        $this->assertEquals('OK (200) Response: ' . $exceptionB->getResponse()->body(), $exceptionB->getMessage());
        $this->assertNull($exceptionB->getPrevious());
    }

    public function testYouCanCustomiseIfSaloonShouldThrowAnExceptionOnARequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Success']),
            MockResponse::make(['message' => 'Yee-naw: Horse Not Found']),
        ]);

        $responseA = TestConnector::make()->send(new BadResponseRequest(), $mockClient);

        $this->assertFalse($responseA->shouldThrowRequestException());
        $this->assertNull($responseA->toException());

        $responseB = TestConnector::make()->send(new BadResponseRequest(), $mockClient);
        $this->assertTrue($responseB->shouldThrowRequestException());
        $exceptionB = $responseB->toException();

        $this->assertInstanceOf(RequestException::class, $exceptionB);
        $this->assertInstanceOf(PendingRequest::class, $exceptionB->getPendingRequest());
        $this->assertInstanceOf(Response::class, $exceptionB->getResponse());
        $this->assertEquals('OK (200) Response: ' . $exceptionB->getResponse()->body(), $exceptionB->getMessage());
        $this->assertNull($exceptionB->getPrevious());
    }

    public function testWhenBothTheConnectorAndRequestHaveCustomLogicToDetermineDifferentFailuresTheyWorkTogether()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Success']),
            MockResponse::make(['message' => 'Error: Invalid Cowboy Hat']),
            MockResponse::make(['message' => 'Yee-naw: Horse Not Found']),
        ]);

        $responseA = BadResponseConnector::make()->send(new BadResponseRequest(), $mockClient);

        $this->assertFalse($responseA->shouldThrowRequestException());
        $this->assertNull($responseA->toException());

        $responseB = BadResponseConnector::make()->send(new BadResponseRequest(), $mockClient);
        $this->assertTrue($responseB->shouldThrowRequestException());
        $exceptionB = $responseB->toException();

        $this->assertInstanceOf(RequestException::class, $exceptionB);
        $this->assertInstanceOf(PendingRequest::class, $exceptionB->getPendingRequest());
        $this->assertInstanceOf(Response::class, $exceptionB->getResponse());
        $this->assertEquals('OK (200) Response: ' . $exceptionB->getResponse()->body(), $exceptionB->getMessage());
        $this->assertNull($exceptionB->getPrevious());

        $responseC = BadResponseConnector::make()->send(new BadResponseRequest(), $mockClient);
        $this->assertTrue($responseC->shouldThrowRequestException());
        $exceptionC = $responseC->toException();

        $this->assertInstanceOf(RequestException::class, $exceptionC);
        $this->assertInstanceOf(Response::class, $exceptionC->getResponse());
        $this->assertEquals('OK (200) Response: ' . $exceptionC->getResponse()->body(), $exceptionC->getMessage());
        $this->assertNull($exceptionC->getPrevious());
    }

    public function testYouCanCustomiseIfSaloonDeterminesIfARequestHasFailedOnAConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Success']),
            MockResponse::make(['message' => 'Error: Invalid Cowboy Hat']),
        ]);

        $responseA = CustomFailHandlerConnector::make()->send(new UserRequest(), $mockClient);

        $this->assertFalse($responseA->failed());

        $responseB = CustomFailHandlerConnector::make()->send(new UserRequest(), $mockClient);

        $this->assertTrue($responseB->failed());
    }

    public function testYouCanCustomiseIfSaloonDeterminesIfARequestHasFailedOnARequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Success']),
            MockResponse::make(['message' => 'Yee-naw: Horse Not Found']),
        ]);

        $responseA = TestConnector::make()->send(new CustomFailHandlerRequest(), $mockClient);

        $this->assertFalse($responseA->failed());

        $responseB = TestConnector::make()->send(new CustomFailHandlerRequest(), $mockClient);

        $this->assertTrue($responseB->failed());
    }

    public function testARequestCanMarkARequestAsNotFailed()
    {
        $response = TestConnector::make()->send(new NotFoundFailedRequest());

        $this->assertFalse($response->failed());
    }

    public function testARequestCanMarkARequestAsNotFailedWithAsynchronousRequests()
    {
        $response = TestConnector::make()->sendAsync(new NotFoundFailedRequest())->wait();

        $this->assertFalse($response->failed());
    }

    public function testARequestCanMarkARequestAsNotFailedWithPools()
    {
        $responseCount = 0;
        $exceptionCount = 0;

        $pool = TestConnector::make()->pool([
            new NotFoundFailedRequest(),
        ]);

        $pool->withResponseHandler(function (Response $response) use (&$responseCount) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(404, $response->status());

            $responseCount++;
        })->withExceptionHandler(function (RequestException $exception) use (&$exceptionCount) {
            $response = $exception->getResponse();

            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals(404, $response->status());

            $exceptionCount++;
        });

        $promise = $pool->send();

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $promise->wait();

        $this->assertEquals(1, $responseCount);
        $this->assertEquals(0, $exceptionCount);
    }

    public function urlDataProvider()
    {
        return [
            ['https://saloon.saloon.test'],
            ['https://saloon.doesnt-exist'],
        ];
    }

    /**
     * @dataProvider urlDataProvider
     */
    public function testTheSenderWillThrowAFatalRequestExceptionIfItCannotConnectToASiteUsingSynchronous($url)
    {
        $connector = new TestConnector($url);
        $request = new UserRequest();

        $this->expectException(FatalRequestException::class);

        $connector->send($request);
    }

    /**
     * @dataProvider urlDataProvider
     */
    public function testTheSenderWillThrowAFatalRequestExceptionIfItCannotConnectToASiteUsingAsynchronous($url)
    {
        $connector = new TestConnector($url);
        $request = new UserRequest();

        $this->expectException(FatalRequestException::class);

        $connector->sendAsync($request)->wait();
    }
}
