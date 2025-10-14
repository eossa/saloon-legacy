<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;

class DelayRequestTest extends TestCase
{
    public function testAsyncRequestDelayWorks()
    {
        $request = new UserRequest();
        $request->delay()->set(1000);

        $this->assertEquals(1000, $request->delay()->get());

        $start = microtime(true);
        connector()->sendAsync($request)->wait();
        $this->assertGreaterThanOrEqual(1, round(microtime(true) - $start));

        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $start = microtime(true);
        connector()->sendAsync($request, $mockClient)->wait();
        $this->assertGreaterThanOrEqual(1, round(microtime(true) - $start));
    }

    public function testRequestDelayTakesPriorityOverConnectorDelay()
    {
        $request = new UserRequest();

        $request
            ->delay()
            ->set(1000);

        $this->assertEquals(1000, $request->delay()->get());

        $connector = connector();

        $start = microtime(true);
        $connector->send($request, new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]));
        $waitTime = round(microtime(true) - $start);
        $this->assertGreaterThanOrEqual(1, $waitTime);

        $connector = connector();
        $connector->delay()->set(5000);
        $start = microtime(true);
        $connector->send($request, new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]));
        $waitTime = round(microtime(true) - $start);

        $this->assertLessThan(5, $waitTime);
    }

    public function testRequestDelayWorks()
    {
        $request = new UserRequest();
        $request->delay()->set(1000);

        $this->assertEquals(1000, $request->delay()->get());

        $start = microtime(true);
        connector()->send($request);
        $this->assertGreaterThanOrEqual(1, round(microtime(true) - $start));

        $mockClient = new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]);

        $start = microtime(true);
        connector()->send($request, $mockClient);
        $this->assertGreaterThanOrEqual(1, round(microtime(true) - $start));
    }

    public function testConnectorDelayWorks()
    {
        $request = new UserRequest();

        $this->assertTrue($request->delay()->isEmpty());

        $connector = connector();
        $connector->delay()->set(1000);

        $this->assertTrue($connector->delay()->isNotEmpty());
        $this->assertEquals(1000, $connector->delay()->get());

        $start = microtime(true);
        $connector->send($request, new MockClient([
            MockResponse::make(['name' => 'Sam']),
        ]));
        $this->assertGreaterThanOrEqual(1, round(microtime(true) - $start));

        $this->assertEquals(1000, $connector->delay()->get());

        $start = microtime(true);
        $connector->send($request);
        $this->assertGreaterThanOrEqual(1, round(microtime(true) - $start));
    }
}
