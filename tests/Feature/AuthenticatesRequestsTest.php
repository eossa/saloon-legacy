<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class AuthenticatesRequestsTest extends TestCase
{
    public function testYouCanProvideDigestAuthenticationAndGuzzleWillSendIt()
    {
        $connector = new TestConnector();
        $request = new UserRequest();

        $request->withDigestAuth('Sammyjo20', 'Cowboy1', 'Howdy');

        $asserted = false;

        $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
            return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
                $this->assertArrayHasKey(RequestOptions::AUTH, $options);
                $this->assertEquals([
                    'Sammyjo20',
                    'Cowboy1',
                    'Howdy',
                ], $options[RequestOptions::AUTH]);

                $asserted = true;

                $factory = new HttpFactory();

                return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
            };
        });

        $connector->send($request);

        $this->assertTrue($asserted);
    }
}
