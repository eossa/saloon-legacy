<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class ConfigTest extends TestCase
{
    public function testDefaultGuzzleConfigOptionsAreSent()
    {
        $connector = new TestConnector();

        $connector->sender()->addMiddleware(function (callable $handler) {
            return function (RequestInterface $guzzleRequest, array $options) {
                $this->assertArrayHasKey('http_errors', $options);
                $this->assertEquals(true, $options['http_errors']);

                $this->assertArrayHasKey('connect_timeout', $options);
                $this->assertEquals(10, $options['connect_timeout']);

                $this->assertArrayHasKey('timeout', $options);
                $this->assertEquals(30, $options['timeout']);

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send(new UserRequest());
    }

    public function testYouCanPassAdditionalGuzzleConfigOptionsAndTheyAreMergedFromTheConnectorAndRequest()
    {
        $connector = new TestConnector();

        $connector->config()->add('debug', true);

        $connector->sender()->addMiddleware(function (callable $handler) {
            return function (RequestInterface $guzzleRequest, array $options) {
                $this->assertArrayHasKey('http_errors', $options);
                $this->assertEquals(true, $options['http_errors']);

                $this->assertArrayHasKey('connect_timeout', $options);
                $this->assertEquals(10, $options['connect_timeout']);

                $this->assertArrayHasKey('timeout', $options);
                $this->assertEquals(30, $options['timeout']);

                $this->assertArrayHasKey('debug', $options);
                $this->assertEquals(true, $options['debug']);

                $this->assertArrayHasKey('verify', $options);
                $this->assertEquals(false, $options['verify']);

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $request = new UserRequest();

        $request->config()->add('verify', false);

        $connector->send($request);
    }
}
