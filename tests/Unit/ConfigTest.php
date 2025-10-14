<?php

namespace Saloon\Tests\Unit;

use Saloon\Config;
use Saloon\Http\Response;
use PHPUnit\Framework\TestCase;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Exceptions\StrayRequestException;
use Saloon\Tests\Fixtures\Senders\ArraySender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class ConfigTest extends TestCase
{
    protected function tearDown()
    {
        Config::clearGlobalMiddleware();
        Config::$defaultSender = GuzzleSender::class;
    }

    public function testTheConfigCanSpecifyGlobalMiddleware()
    {
        $mockClient = new MockClient([
            new MockResponse(['name' => 'Jake Owen - Beachin']),
        ]);

        $count = 0;

        Config::globalMiddleware()->onRequest(function (PendingRequest $pendingRequest) use (&$count) {
            $count++;
        });

        Config::globalMiddleware()->onResponse(function (Response $response) use (&$count) {
            $count++;
        });

        TestConnector::make()->send(new UserRequest(), $mockClient);

        $this->assertEquals(2, $count);
    }

    public function testYouCanChangeTheGlobalDefaultSenderUsed()
    {
        Config::$defaultSender = ArraySender::class;

        $connector = new TestConnector();

        $connector->send(new UserRequest());

        $this->assertInstanceOf(ArraySender::class, $connector->sender());

        Config::$defaultSender = GuzzleSender::class;

        $connector = new TestConnector();

        $response = $connector->send(new UserRequest());

        $this->assertInstanceOf(GuzzleSender::class, $response->getPendingRequest()->getConnector()->sender());
    }

    public function testYouCanChangeHowTheGlobalDefaultSenderIsResolved()
    {
        $sender = TestConnector::make()->sender();

        $this->assertInstanceOf(GuzzleSender::class, $sender);

        Config::setSenderResolver(function () {
            return new ArraySender();
        });

        $sender = TestConnector::make()->sender();

        $this->assertInstanceOf(ArraySender::class, $sender);

        Config::setSenderResolver(null);

        $sender = TestConnector::make()->sender();

        $this->assertInstanceOf(GuzzleSender::class, $sender);
    }

    public function testYouCanPreventStrayApiRequests()
    {
        Config::preventStrayRequests();

        $this->expectException(StrayRequestException::class);
        $this->expectExceptionMessage('Attempted to make a real API request! Make sure to use a mock response or fixture.');

        TestConnector::make()->send(new UserRequest());

        Config::clearGlobalMiddleware();
    }
}
