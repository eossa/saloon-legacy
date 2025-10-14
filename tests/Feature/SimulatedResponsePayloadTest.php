<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\FakeResponse;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class SimulatedResponsePayloadTest extends TestCase
{
    public function testIfASimulatedResponsePayloadWasProvidedBeforeMockResponseItWillTakePriority()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Sam'], 200, ['X-Greeting' => 'Howdy']),
        ]);

        $fakeResponse = new FakeResponse(['name' => 'Gareth'], 201, ['X-Greeting' => 'Hello']);

        $request = new UserRequest();
        $request->middleware()->onRequest(function () use ($fakeResponse) {
            return $fakeResponse;
        });

        $response = TestConnector::make()->send($request, $mockClient);

        $this->assertEquals(['name' => 'Gareth'], $response->json());
        $this->assertEquals(201, $response->status());
        $this->assertEquals('Hello', $response->header('X-Greeting'));
    }
}
