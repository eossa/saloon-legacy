<?php

namespace Saloon\Tests\Feature\Body;

use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasFormBodyRequest;
use PHPUnit\Framework\TestCase;

class HasFormBodyTest extends TestCase
{
    public function testTheDefaultBodyIsLoadedWithTheContentTypeHeader()
    {
        $request = new HasFormBodyRequest();

        $this->assertEquals([
            'name' => 'Sam',
            'catchphrase' => 'Yeehaw!',
        ], $request->body()->all());

        $connector = new TestConnector();
        $pendingRequest = $connector->createPendingRequest($request);

        $this->assertEquals('application/x-www-form-urlencoded', $pendingRequest->headers()->get('Content-Type'));
    }

    public function testTheGuzzleSenderProperlySendsIt()
    {
        $connector = new TestConnector();
        $request = new HasFormBodyRequest();

        $request->middleware()->onRequest(function (PendingRequest $pendingRequest) {
            $this->assertEquals('application/x-www-form-urlencoded', $pendingRequest->headers()->get('Content-Type'));
        });

        $asserted = false;

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $this->assertEquals(['application/x-www-form-urlencoded'], $guzzleRequest->getHeader('Content-Type'));
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
