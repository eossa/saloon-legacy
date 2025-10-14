<?php

namespace Saloon\Tests\Feature\Body;

use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasStreamBodyRequest;
use PHPUnit\Framework\TestCase;

class HasStreamBodyTest extends TestCase
{
    public function testTheDefaultBodyIsLoaded()
    {
        $request = new HasStreamBodyRequest();

        $this->assertTrue(is_resource($request->body()->all()));
    }

    public function testTheGuzzleSenderProperlySendsIt()
    {
        $connector = new TestConnector();
        $request = new HasStreamBodyRequest();

        $request->headers()->add('Content-Type', 'application/custom');

        $asserted = false;

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $this->assertEquals(['application/custom'], $guzzleRequest->getHeader('Content-Type'));
                $this->assertEquals('Howdy, Partner', (string)$guzzleRequest->getBody());

                $asserted = true;

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send($request);

        $this->assertTrue($asserted);
    }
}
