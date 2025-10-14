<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Exceptions\TestResponseException;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Tests\Fixtures\Connectors\DifferentServiceConnector;
use Saloon\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;
use PHPUnit\Framework\TestCase;
use Exception;

class MockClientTest extends TestCase
{
    public function testYouCanCreateSequenceMocks()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);

        $mockClient = new MockClient([$responseA, $responseB]);

        $this->assertEquals($responseA, $mockClient->getNextFromSequence());
        $this->assertEquals($responseB, $mockClient->getNextFromSequence());
        $this->assertTrue($mockClient->isEmpty());
    }

    public function testYouCanCreateConnectorMocks()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);

        $connectorA = new TestConnector();
        $connectorB = new QueryParameterConnector();

        $connectorARequest = new UserRequest();
        $connectorBRequest = new QueryParameterConnectorRequest();

        $mockClient = new MockClient([
            TestConnector::class => $responseA,
            QueryParameterConnector::class => $responseB,
        ]);

        $this->assertEquals($responseA, $mockClient->guessNextResponse($connectorA->createPendingRequest($connectorARequest)));
        $this->assertEquals($responseB, $mockClient->guessNextResponse($connectorB->createPendingRequest($connectorBRequest)));
        $this->assertFalse($mockClient->isEmpty());
    }

    public function testYouCanCreateRequestMocks()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);

        $connectorA = new TestConnector();
        $connectorB = new QueryParameterConnector();

        $requestA = new UserRequest();
        $requestB = new QueryParameterConnectorRequest();

        $mockClient = new MockClient([
            UserRequest::class => $responseA,
            QueryParameterConnectorRequest::class => $responseB,
        ]);

        $this->assertEquals($responseA, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)));
        $this->assertEquals($responseB, $mockClient->guessNextResponse($connectorB->createPendingRequest($requestB)));
        $this->assertFalse($mockClient->isEmpty());
    }

    public function testYouCanCreateUrlMocks()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);
        $responseC = MockResponse::make(['name' => 'Sam Carré']);

        $connectorA = new TestConnector();
        $connectorB = new DifferentServiceConnector();

        $requestA = new UserRequest();
        $requestB = new ErrorRequest();
        $requestC = new DifferentServiceUserRequest();

        $mockClient = new MockClient([
            'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
            'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
            'google.com/*' => $responseC, // Test Different Route,
        ]);

        $this->assertEquals($responseA, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)));
        $this->assertEquals($responseB, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestB)));
        $this->assertEquals($responseC, $mockClient->guessNextResponse($connectorB->createPendingRequest($requestC)));
    }

    public function testYouCanCreateWildcardUrlMocks()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);
        $responseC = MockResponse::make(['name' => 'Sam Carré']);

        $connectorA = new TestConnector();
        $connectorB = new DifferentServiceConnector();

        $requestA = new UserRequest();
        $requestB = new ErrorRequest();
        $requestC = new DifferentServiceUserRequest();

        $mockClient = new MockClient([
            'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
            'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
            '*' => $responseC,
        ]);

        $this->assertEquals($responseA, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)));
        $this->assertEquals($responseB, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestB)));
        $this->assertEquals($responseC, $mockClient->guessNextResponse($connectorB->createPendingRequest($requestC)));
    }

    public function testSaloonThrowsAnExceptionIfItCantWorkOutTheUrlResponse()
    {
        $responseA = MockResponse::make(['name' => 'Sammyjo20']);
        $responseB = MockResponse::make(['name' => 'Alex']);
        $responseC = MockResponse::make(['name' => 'Sam Carré']);

        $connectorA = new TestConnector();
        $connectorB = new DifferentServiceConnector();

        $requestA = new UserRequest();
        $requestB = new ErrorRequest();
        $requestC = new DifferentServiceUserRequest();

        $mockClient = new MockClient([
            'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
            'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        ]);

        $this->assertEquals($responseA, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)));
        $this->assertEquals($responseB, $mockClient->guessNextResponse($connectorA->createPendingRequest($requestB)));

        $this->expectException(NoMockResponseFoundException::class);
        $this->expectExceptionMessage('Saloon was unable to guess a mock response for your request [https://google.com/user], consider using a wildcard url mock or a connector mock.');

        $mockClient->guessNextResponse($connectorB->createPendingRequest($requestC));
    }

    public function testYouCanGetAnArrayOfTheRecordedRequests()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor']),
            MockResponse::make(['name' => 'Marcel']),
        ]);

        $connector = new TestConnector();

        $responseA = $connector->send(new UserRequest(), $mockClient);
        $responseB = $connector->send(new UserRequest(), $mockClient);
        $responseC = $connector->send(new UserRequest(), $mockClient);

        $responses = $mockClient->getRecordedResponses();

        $this->assertEquals([
            $responseA,
            $responseB,
            $responseC,
        ], $responses);
    }

    public function testYouCanGetTheLastRecordedRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Taylor']),
            MockResponse::make(['name' => 'Marcel']),
        ]);

        $connector = new TestConnector();

        $responseA = $connector->send(new UserRequest(), $mockClient);
        $responseB = $connector->send(new UserRequest(), $mockClient);
        $responseC = $connector->send(new UserRequest(), $mockClient);

        $lastResponse = $mockClient->getLastResponse();

        $this->assertSame($responseC, $lastResponse);
    }

    public function testIfThereAreNoRecordedResponsesTheGetLastResponseWillReturnNull()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $this->assertNull($mockClient->getLastResponse());
    }

    public function testIfThereAreNoRecordedResponsesTheGetLastRequestWillReturnNull()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $this->assertNull($mockClient->getLastRequest());
    }

    public function testIfTheResponseIsNotTheLastResponseItWillUseTheLoopToFindIt()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        $responseA = connector()->send(new ErrorRequest(), $mockClient);
        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertSame($responseB, $mockClient->getLastResponse());

        // Uses last response
        $this->assertSame($responseB, $mockClient->findResponseByRequest(UserRequest::class));

        // Does not use the last response
        $this->assertSame($responseA, $mockClient->findResponseByRequest(ErrorRequest::class));
    }

    public function testItWillFindTheResponseByUrlIfItIsNotTheLastResponse()
    {
        $mockClient = new MockClient([
            '/user' => MockResponse::make(['name' => 'Sam']),
            '/error' => MockResponse::make(['error' => 'Server Error'], 500),
        ]);

        $responseA = connector()->send(new ErrorRequest(), $mockClient);
        $responseB = connector()->send(new UserRequest(), $mockClient);

        $this->assertSame($responseB, $mockClient->getLastResponse());

        // Uses last response
        $this->assertSame($responseB, $mockClient->findResponseByRequestUrl('/user'));

        // Does not use the last response
        $this->assertSame($responseA, $mockClient->findResponseByRequestUrl('/error'));
    }

    public function testYouCanMockExceptionsWithAClosure()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Patrick'])->throwException(function ($pendingRequest) {
                return new TestResponseException('Unable to connect!', $pendingRequest);
            }),
        ]);

        $okResponse = connector()->send(new UserRequest(), $mockClient);

        $this->assertEquals(['name' => 'Sam'], $okResponse->json());

        $this->expectException(TestResponseException::class);
        $this->expectExceptionMessage('Unable to connect!');

        $response = connector()->send(new UserRequest(), $mockClient);
        $response->throw();
    }

    public function testYouCanMockNormalExceptions()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Michael'])->throwException(new Exception('Custom Exception!')),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Custom Exception!');

        $response = connector()->send(new UserRequest(), $mockClient);
        $response->throw();
    }
}
