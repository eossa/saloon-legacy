<?php

namespace Saloon\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class GlobalMockClientTest extends TestCase
{
    protected function tearDown()
    {
        MockClient::destroyGlobal();
    }

    public function testCanCreateAGlobalMockClient()
    {
        $responses = [
            MockResponse::make(['name' => 'Sam'])
        ];

        $mockClient = MockClient::setGlobal($responses);

        $this->assertInstanceOf('Saloon\Http\Faking\MockClient', $mockClient);
        $this->assertSame($mockClient, MockClient::getGlobal());

        $connector = new TestConnector();
        $response = $connector->send(new UserRequest());

        $this->assertTrue($response->isMocked());
        $this->assertEquals(['name' => 'Sam'], $response->json());

        $mockClient->assertSent('Saloon\Tests\Fixtures\Requests\UserRequest');
    }

    public function testTheMockClientCanBeDestroyed()
    {
        $mockClient = MockClient::setGlobal();

        $this->assertSame($mockClient, MockClient::getGlobal());

        MockClient::destroyGlobal();

        $this->assertNull(MockClient::getGlobal());
    }

    public function testALocalMockClientIsGivenPriorityOverTheGlobalMockClient()
    {
        $globalResponses = [
            MockResponse::make(['name' => 'Sam'])
        ];
        MockClient::setGlobal($globalResponses);

        $localResponses = [
            MockResponse::make(['name' => 'Taylor'])
        ];
        $localMockClient = new MockClient($localResponses);

        $connector = new TestConnector();
        $connector->withMockClient($localMockClient);

        $response = $connector->send(new UserRequest());

        $this->assertTrue($response->isMocked());
        $this->assertEquals(['name' => 'Taylor'], $response->json());

        $localMockClient->assertSentCount(1);
        MockClient::getGlobal()->assertNothingSent();
    }
}
