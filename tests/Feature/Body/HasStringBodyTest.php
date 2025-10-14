<?php

namespace Saloon\Tests\Feature\Body;

use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasStringBodyRequest;
use PHPUnit\Framework\TestCase;

class HasStringBodyTest extends TestCase
{
    public function testTheDefaultBodyIsLoaded()
    {
        $request = new HasStringBodyRequest();

        $this->assertEquals('name: Sam', $request->body()->all());
    }

    public function testTheGuzzleSenderProperlySendsIt()
    {
        $connector = new TestConnector();
        $request = new HasStringBodyRequest();

        $request->headers()->add('Content-Type', 'application/custom');

        $asserted = false;

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $this->assertEquals(['application/custom'], $guzzleRequest->getHeader('Content-Type'));
                $this->assertEquals((string)$request->body(), (string)$guzzleRequest->getBody());

                $asserted = true;

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send($request);

        $this->assertTrue($asserted);
    }
}
