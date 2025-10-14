<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use PHPUnit\Framework\TestCase;

class MocksRequestsTest extends TestCase
{
    public function testYouCanProvideAMockClientOnAConnectorAndAllRequestsWillBeMocked()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClient);

        $responseA = $connector->send(new UserRequest());
        $responseB = $connector->send(new UserRequest());

        $this->assertTrue($responseA->isMocked());
        $this->assertTrue($responseB->isMocked());
    }

    public function testYouCanProvideAMockClientOnARequestAndAllRequestsWillBeMocked()
    {
        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $request = new UserRequest();
        $request->withMockClient($mockClient);

        $response = connector()->send($request);

        $this->assertTrue($response->isMocked());
    }

    public function testRequestMockClientsAreAlwaysPrioritized()
    {
        $mockClientA = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $mockClientB = new MockClient([
            MockResponse::make(['name' => 'Mantas']),
        ]);

        $connector = new TestConnector();
        $connector->withMockClient($mockClientA);

        $request = new UserRequest();
        $request->withMockClient($mockClientB);

        $response = $connector->send($request);

        $this->assertTrue($response->isMocked());
        $this->assertEquals(['name' => 'Mantas'], $response->json());
    }
}
