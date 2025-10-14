<?php

namespace Saloon\Tests\Unit\Plugins;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\TimeoutRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Connectors\TimeoutConnector;
use GuzzleHttp\Psr7\HttpFactory;

class HasTimeoutPluginTest extends TestCase
{
    public function testARequestIsGivenADefaultTimeoutAndConnectTimeout()
    {
        $connector = new TestConnector();
        $request = UserRequest::make();

        $connector->sender()->addMiddleware(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $this->assertEquals(10, $options['connect_timeout']);
                $this->assertEquals(30, $options['timeout']);

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        }, 'test');

        $connector->send($request);
    }

    public function testARequestCanSetATimeoutAndConnectTimeout()
    {
        $request = new TimeoutRequest();
        $pendingRequest = connector()->createPendingRequest($request);

        $config = $pendingRequest->config()->all();

        $this->assertArrayHasKey('connect_timeout', $config);
        $this->assertEquals(1, $config['connect_timeout']);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertEquals(2, $config['timeout']);
    }

    public function testAConnectorIsGivenADefaultTimeoutAndConnectTimeout()
    {
        $connector = new TimeoutConnector();

        $connector->sender()->addMiddleware(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $this->assertEquals(10.0, $options['connect_timeout']);
                $this->assertEquals(5.0, $options['timeout']);

                return new FulfilledPromise(new Response());
            };
        });

        $pendingRequest = $connector->createPendingRequest(new UserRequest());

        $config = $pendingRequest->config()->all();

        $this->assertArrayHasKey('connect_timeout', $config);
        $this->assertEquals(10, $config['connect_timeout']);
        $this->assertArrayHasKey('timeout', $config);
        $this->assertEquals(5, $config['timeout']);

        $connector->send(new UserRequest());
    }
}
