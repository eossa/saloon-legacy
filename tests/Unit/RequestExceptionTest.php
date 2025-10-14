<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Faking\MockClient;
use Saloon\Helpers\StatusCodeHelper;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Tests\Fixtures\Requests\AlwaysHasFailureRequest;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Exceptions\Request\Statuses\GatewayTimeoutException;
use Saloon\Exceptions\Request\Statuses\RequestTimeOutException;
use Saloon\Exceptions\Request\Statuses\PaymentRequiredException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;
use Saloon\Exceptions\Request\Statuses\MethodNotAllowedException;
use Saloon\Exceptions\Request\Statuses\ServiceUnavailableException;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\Statuses\UnprocessableEntityException;
use PHPUnit\Framework\TestCase;

class RequestExceptionTest extends TestCase
{
    /**
     * @dataProvider statusCodeExceptionProvider
     */
    public function testTheResponseWillReturnDifferentExceptionsBasedOnStatus($status, $expectedException)
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Oh yee-naw!'], $status),
        ]);

        $response = TestConnector::make()->send(new UserRequest(), $mockClient);
        $exception = $response->toException();

        $message = sprintf('%s (%s) Response: %s', StatusCodeHelper::getMessage($status), $status, $response->body());

        $this->assertInstanceOf($expectedException, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function statusCodeExceptionProvider()
    {
        return [
            'unauthorized' => [401, UnauthorizedException::class],
            'payment_required' => [402, PaymentRequiredException::class],
            'forbidden' => [403, ForbiddenException::class],
            'not_found' => [404, NotFoundException::class],
            'method_not_allowed' => [405, MethodNotAllowedException::class],
            'request_timeout' => [408, RequestTimeOutException::class],
            'unprocessable_entity' => [422, UnprocessableEntityException::class],
            'too_many_requests' => [429, TooManyRequestsException::class],
            'internal_server_error' => [500, InternalServerErrorException::class],
            'service_unavailable' => [503, ServiceUnavailableException::class],
            'gateway_timeout' => [504, GatewayTimeoutException::class],
            'client_error_418' => [418, ClientException::class],
            'client_error_411' => [411, ClientException::class],
        ];
    }

    /**
     * @dataProvider customFailureStatusProvider
     */
    public function testWhenTheFailedMethodIsCustomisedTheResponseWillReturnOkRequestExceptions($status, $expectedException)
    {
        $mockClient = new MockClient([
            MockResponse::make(['message' => 'Oh yee-naw!'], $status),
        ]);

        $response = TestConnector::make()->send(new AlwaysHasFailureRequest(), $mockClient);
        $exception = $response->toException();

        $message = sprintf('%s (%s) Response: %s', StatusCodeHelper::getMessage($status), $status, $response->body());

        $this->assertInstanceOf($expectedException, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    public function customFailureStatusProvider()
    {
        return [
            'redirect_302' => [302, RequestException::class],
            'success_200' => [200, RequestException::class],
            'created_201' => [201, RequestException::class],
            'continue_100' => [100, RequestException::class],
        ];
    }
}
