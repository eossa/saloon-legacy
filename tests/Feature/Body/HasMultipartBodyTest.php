<?php

namespace Saloon\Tests\Feature\Body;

use Saloon\Data\MultipartValue;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Saloon\Tests\Fixtures\Requests\MixedMultipartRequest;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;
use Saloon\Tests\Fixtures\Connectors\HasMultipartBodyConnector;
use PHPUnit\Framework\TestCase;

class HasMultipartBodyTest extends TestCase
{
    public function testTheDefaultBodyIsLoadedWithTheContentTypeHeader()
    {
        $request = new HasMultipartBodyRequest();

        $this->assertEquals([
            new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        ], $request->body()->all());

        $connector = new TestConnector();
        $pendingRequest = $connector->createPendingRequest($request);

        /** @var MultipartBodyRepository $body */
        $body = $pendingRequest->body();

        $this->assertEquals('multipart/form-data; boundary=' . $body->getBoundary(), $pendingRequest->headers()->get('Content-Type'));
    }

    public function testWhenBothTheConnectorAndTheRequestHaveTheSameRequestBodiesTheyWillBeMerged()
    {
        $connector = new HasMultipartBodyConnector();
        $request = new HasMultipartBodyRequest();

        $this->assertEquals([
            new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
            new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
        ], $connector->body()->all());

        $this->assertEquals([
            new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        ], $request->body()->all());

        // Nickname should be overwritten to "Sam" and "drink" should be merged in

        $pendingRequest = $connector->createPendingRequest($request);
        $pendingRequestBody = $pendingRequest->body();

        $this->assertInstanceOf(MultipartBodyRepository::class, $pendingRequestBody);

        $this->assertEquals([
            new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
            new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
            new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        ], $pendingRequestBody->all());
    }

    public function testTheGuzzleSenderProperlySendsIt()
    {
        $connector = new TestConnector();
        $request = new HasMultipartBodyRequest();

        $asserted = false;

        $request->middleware()->onRequest(function (PendingRequest $pendingRequest) {
            $this->assertContains('multipart/form-data; boundary=' . $pendingRequest->body()->getBoundary(), $pendingRequest->headers()->get('Content-Type'));
        });

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $this->assertContains('multipart/form-data; boundary=', $guzzleRequest->getHeader('Content-Type')[0]);

                $bodyContent = (string)$guzzleRequest->getBody();
                $this->assertContains('X-Saloon: Yee-haw!', $bodyContent);
                $this->assertContains('Content-Disposition: form-data; name="nickname"; filename="user.txt"', $bodyContent);
                $this->assertContains('Content-Length: 3', $bodyContent);
                $this->assertContains('Sam', $bodyContent);

                $asserted = true;

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send($request);

        $this->assertTrue($asserted);
    }

    public function testCanSendARealMultipartRequestAndFilesAreSent()
    {
        $connector = new TestConnector();
        $request = new MixedMultipartRequest();

        $request->body()->add('name', 'Howdy');
        $request->body()->add('file', file_get_contents('tests/Fixtures/Howdy.txt'), 'hi.txt');

        $response = $connector->send($request);

        $data = $response->json();

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Howdy', $data['name']);
        $this->assertArrayHasKey('file_contents', $data);
        $this->assertEquals('Hello World!' . PHP_EOL, $data['file_contents']);
    }

    public function testCanSendAnEmptyStringAsTheContents()
    {
        $connector = new TestConnector();
        $request = new MixedMultipartRequest();

        $request->body()->add('name', 'Howdy');
        $request->body()->add('file', '', 'hi.txt');

        $response = $connector->send($request);

        $data = $response->json();

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('Howdy', $data['name']);
        $this->assertArrayHasKey('file_contents', $data);
        $this->assertEquals('', $data['file_contents']);
    }

    public function testCanSendMultipleMultipartFilesWithTheSameKeyName()
    {
        $connector = new TestConnector();
        $request = new HasMultipartBodyRequest();

        $request->body()->add('nickname', 'Alfie', 'user.txt');
        $request->body()->add('nickname', 'Tom', 'user.txt');

        $asserted = false;

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $bodyContent = $guzzleRequest->getBody()->getContents();
                $this->assertContains('X-Saloon: Yee-haw!', $bodyContent);
                $this->assertContains('Content-Disposition: form-data; name="nickname"; filename="user.txt"', $bodyContent);
                $this->assertContains('Content-Length: 3', $bodyContent);
                $this->assertContains('Sam', $bodyContent);
                $this->assertContains('Alfie', $bodyContent);
                $this->assertContains('Tom', $bodyContent);

                $asserted = true;

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send($request);

        $this->assertTrue($asserted);
    }
}
