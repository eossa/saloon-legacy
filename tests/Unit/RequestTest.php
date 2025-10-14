<?php

namespace Saloon\Tests\Unit;

use LogicException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Responses\CustomResponse;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Tests\Fixtures\Requests\InvalidResponseClass;
use Saloon\Tests\Fixtures\Requests\MissingMethodRequest;
use Saloon\Tests\Fixtures\Requests\CustomEndpointRequest;
use Saloon\Tests\Fixtures\Requests\DefaultEndpointRequest;
use Saloon\Tests\Fixtures\Connectors\CustomBaseUrlConnector;
use Saloon\Tests\Fixtures\Connectors\CustomResponseConnector;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;
use Saloon\Tests\Fixtures\Requests\CustomResponseConnectorRequest;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testIfYouDontPassInAMockClientToTheSaloonRequestItWillNotBeInMockingMode()
    {
        $request = new UserRequest();
        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertFalse($pendingRequest->hasMockClient());
    }

    public function testYouCanPassAMockClientToTheSaloonRequestAndItWillBeInMockMode()
    {
        $request = new UserRequest();
        $mockClient = new MockClient([MockResponse::make([])]);

        $request->withMockClient($mockClient);

        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertTrue($pendingRequest->hasMockClient());
        $this->assertSame($mockClient, $pendingRequest->getMockClient());
    }

    public function testYouCantSendARequestWithAMockClientWithoutAnyResponses()
    {
        $mockClient = new MockClient();
        $request = new UserRequest();

        $this->expectException(NoMockResponseFoundException::class);

        connector()->send($request, $mockClient);
    }

    public function testSaloonWorksWithACustomResponseClassInConnector()
    {
        $request = new CustomResponseConnector();

        $this->assertEquals(CustomResponse::class, $request->resolveResponseClass());
    }

    public function testSaloonCanHandleWithCustomResponseInConnector()
    {
        $request = new CustomResponseConnectorRequest();
        $pendingRequest = (new CustomResponseConnector())->createPendingRequest($request);

        $this->assertEquals(CustomResponse::class, $pendingRequest->getResponseClass());
    }

    public function testSaloonCanHandleWithCustomResponseInRequest()
    {
        $request = new UserRequestWithCustomResponse();

        $this->assertEquals(UserResponse::class, $request->resolveResponseClass());
    }

    public function testSaloonThrowsAnExceptionIfTheCustomResponseIsNotAResponseClass()
    {
        $invalidConnectorClassRequest = new InvalidResponseClass();

        $this->expectException(InvalidResponseClassException::class);

        $connector = new TestConnector();

        $connector->withMockClient(new MockClient([
            InvalidResponseClass::class => MockResponse::make([], 200),
        ]));

        $connector->send($invalidConnectorClassRequest);
    }

    public function testDefineEndpointMethodMayBeBlankInRequestClassToUseTheBaseUrl()
    {
        $pendingRequest = connector()->createPendingRequest(new DefaultEndpointRequest());

        $this->assertEquals(apiUrl(), $pendingRequest->getUrl());
    }

    public function testARequestClassCanBeInstantiatedUsingTheMakeMethod()
    {
        $requestA = UserRequest::make();

        $this->assertInstanceOf(UserRequest::class, $requestA);
        $this->assertNull($requestA->userId);
        $this->assertNull($requestA->groupId);

        $requestB = UserRequest::make(1, 2);

        $this->assertInstanceOf(UserRequest::class, $requestB);
        $this->assertEquals(1, $requestB->userId);
        $this->assertEquals(2, $requestB->groupId);
    }

    /**
     * @dataProvider urlJoinProvider
     */
    public function testYouCanJoinVariousUrlsTogether($baseUrl, $endpoint, $expected)
    {
        $connector = new CustomBaseUrlConnector();
        $request = new CustomEndpointRequest();

        $connector->setBaseUrl($baseUrl);
        $request->setEndpoint($endpoint);

        $this->assertEquals($expected, $connector->createPendingRequest($request)->getUrl());
    }

    public function urlJoinProvider()
    {
        return [
            'base_with_slash_endpoint_with_slash' => ['https://google.com', '/search', 'https://google.com/search'],
            'base_with_slash_endpoint_without_slash' => ['https://google.com', 'search', 'https://google.com/search'],
            'base_with_trailing_slash_endpoint_with_slash' => ['https://google.com/', '/search', 'https://google.com/search'],
            'base_with_trailing_slash_endpoint_without_slash' => ['https://google.com/', 'search', 'https://google.com/search'],
            'base_with_double_slash_endpoint_with_double_slash' => ['https://google.com//', '//search', 'https://google.com/search'],
            'empty_base_full_endpoint' => ['', 'https://google.com/search', 'https://google.com/search'],
            'empty_base_relative_endpoint' => ['', 'google.com/search', '/google.com/search'],
            'base_url_overridden_by_full_endpoint' => ['https://google.com', 'https://api.google.com/search', 'https://api.google.com/search'],
        ];
    }

    public function testItThrowsAnExceptionIfYouForgetToAddAMethod()
    {
        $connector = new TestConnector();
        $request = new MissingMethodRequest();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Your request is missing a HTTP method. You must add a method property like [protected Method $method = Method::GET]');

        $connector->send($request);
    }
}
