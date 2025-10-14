<?php

namespace Saloon\Tests\Feature\Body;

use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Connectors\HasJsonBodyConnector;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;
use PHPUnit\Framework\TestCase;

class HasJsonBodyTest extends TestCase
{
    public function testTheDefaultBodyIsLoadedWithTheContentTypeHeader()
    {
        $request = new HasJsonBodyRequest();

        $this->assertEquals([
            'name' => 'Sam',
            'catchphrase' => 'Yeehaw!',
        ], $request->body()->all());

        $connector = new TestConnector();
        $pendingRequest = $connector->createPendingRequest($request);

        $this->assertEquals('application/json', $pendingRequest->headers()->get('Content-Type'));
    }

    public function testTheContentTypeHeaderIsSetInThePendingRequest()
    {
        $request = new HasJsonBodyRequest();

        $pendingRequest = TestConnector::make()->createPendingRequest($request);

        $this->assertEquals([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $pendingRequest->headers()->all());
    }

    public function testWhenJustTheConnectorHasBodyTheBodyWillBeSent()
    {
        $connector = new HasJsonBodyConnector();
        $request = new UserRequest();

        $this->assertEquals([
            'name' => 'Gareth',
            'drink' => 'Moonshine',
        ], $connector->body()->all());

        $pendingRequest = $connector->createPendingRequest($request);
        $pendingRequestBody = $pendingRequest->body();

        $this->assertInstanceOf(JsonBodyRepository::class, $pendingRequestBody);

        $this->assertEquals([
            'name' => 'Gareth',
            'drink' => 'Moonshine',
        ], $pendingRequestBody->all());
    }

    public function testWhenBothTheConnectorAndTheRequestHaveTheSameRequestBodiesTheyWillBeMerged()
    {
        $connector = new HasJsonBodyConnector();
        $request = new HasJsonBodyRequest();

        $this->assertEquals([
            'name' => 'Gareth',
            'drink' => 'Moonshine',
        ], $connector->body()->all());

        $this->assertEquals([
            'name' => 'Sam',
            'catchphrase' => 'Yeehaw!',
        ], $request->body()->all());

        // Name should be overwritten to "Sam" and "catchphrase" should be merged in

        $pendingRequest = $connector->createPendingRequest($request);
        $pendingRequestBody = $pendingRequest->body();

        $this->assertInstanceOf(JsonBodyRepository::class, $pendingRequestBody);

        $this->assertEquals([
            'drink' => 'Moonshine',
            'name' => 'Sam',
            'catchphrase' => 'Yeehaw!',
        ], $pendingRequestBody->all());
    }

    public function testIfTheConnectorAndRequestImplementDifferentBodyRepositoriesThenAnExceptionIsThrown()
    {
        $connector = new HasJsonBodyConnector();
        $request = new HasMultipartBodyRequest();

        $this->expectException(PendingRequestException::class);
        $this->expectExceptionMessage('Connector and request body types must be the same.');

        $connector->createPendingRequest($request);
    }

    public function testTheGuzzleSenderProperlySendsIt()
    {
        $connector = new TestConnector();
        $request = new HasJsonBodyRequest();

        $request->middleware()->onRequest(function (PendingRequest $pendingRequest) {
            $this->assertEquals('application/json', $pendingRequest->headers()->get('Content-Type'));
        });

        $asserted = false;

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $this->assertEquals(['application/json'], $guzzleRequest->getHeader('Content-Type'));
                $this->assertEquals((string)$request->body(), (string)$guzzleRequest->getBody());

                $asserted = true;

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send($request);

        $this->assertTrue($asserted);
    }

    public function testYouCanSpecifyDifferentJsonFlagsThatTheBodyRepositoryShouldUse()
    {
        $request = new HasJsonBodyRequest();
        $body = $request->body();

        // We'll add a property with slashes

        $body->add('url', 'https://docs.saloon.dev');

        // By default, PHP will escape slashes

        $this->assertEquals('{"name":"Sam","catchphrase":"Yeehaw!","url":"https:\/\/docs.saloon.dev"}', (string)$body);

        $body->setJsonFlags(JSON_UNESCAPED_SLASHES);

        $this->assertEquals('{"name":"Sam","catchphrase":"Yeehaw!","url":"https://docs.saloon.dev"}', (string)$body);

        $this->assertEquals(JSON_UNESCAPED_SLASHES, $body->getJsonFlags());
    }

    public function testTheJsonBodyRepositoryUsesTheJsonThrowOnErrorDefaultFlag()
    {
        $request = new HasJsonBodyRequest();
        $body = $request->body();

        $this->assertEquals(0, $body->getJsonFlags());
    }
}
