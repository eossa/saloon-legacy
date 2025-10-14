<?php

namespace Saloon\Tests\Unit;

use Saloon\Http\Response;
use GuzzleHttp\Promise\Promise;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasConnectorUserRequest;
use Saloon\Tests\Fixtures\Connectors\RequestSelectionConnector;

class ConnectorTest extends TestCase
{
    public function testAConnectorClassCanBeInstantiatedUsingTheMakeMethod()
    {
        $connectorA = TestConnector::make();

        $this->assertInstanceOf(TestConnector::class, $connectorA);

        $connectorB = RequestSelectionConnector::make('yee-haw-1-2-3');

        $this->assertInstanceOf(RequestSelectionConnector::class, $connectorB);
        $this->assertEquals('yee-haw-1-2-3', $connectorB->apiKey);
    }

    public function testTheSameConnectorInstanceIsKeptIfYouInstantiateItOnTheRequestWithHasConnector()
    {
        $request = new HasConnectorUserRequest();
        $connector = $request->connector();

        $this->assertSame($connector, $request->connector());
    }

    public function testYouCanSendARequestThroughTheConnector()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
        ]);

        $connector = new TestConnector();
        $response = $connector->send(new UserRequest(), $mockClient);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam'], $response->json());
    }

    public function testYouCanSendAnAsynchronousRequestThroughTheConnector()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
        ]);

        $connector = new TestConnector();
        $promise = $connector->sendAsync(new UserRequest(), $mockClient);

        $this->assertInstanceOf(Promise::class, $promise);

        $response = $promise->wait();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam'], $response->json());
    }
}
