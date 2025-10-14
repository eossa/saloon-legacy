<?php

namespace Saloon\Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Saloon\Http\Senders\GuzzleSender;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use function GuzzleHttp\choose_handler;

class GuzzleSenderTest extends TestCase
{
    public function testTheGuzzleSenderWillSendToTheRightUrlUsingTheCorrectMethod()
    {
        $connector = new TestConnector();
        $request = new UserRequest();
        $sender = $connector->sender();

        $pendingRequest = $connector->createPendingRequest($request);

        $sender->addMiddleware(function (callable $handler) use ($pendingRequest) {
            return function (RequestInterface $request, array $options) use ($handler, $pendingRequest) {
                $this->assertEquals($pendingRequest->getMethod(), $request->getMethod());

                $uri = $request->getUri();
                $saloonUri = new Uri($pendingRequest->getUrl());

                $this->assertEquals($saloonUri, $uri);

                // Return fulfilled promise to fake response

                return new FulfilledPromise(new Response());
            };
        });

        $connector->send($request);
    }

    public function testTheGuzzleSenderWillSendAllHeadersQueryParametersAndConfig()
    {
        $connector = connector();
        $request = new UserRequest();

        $request->config()->add('timeout', 120);
        $request->config()->add('debug', true);
        $request->query()->add('shanty', 'yes');
        $request->query()->add('sing', 'yes');
        $request->headers()->add('X-Bound-For', 'South-Australia');
        $request->headers()->add('X-Fancy', ['keyOne' => 'valOne', 'keyTwo' => 'valTwo']);

        $sender = $connector->sender();

        $sender->addMiddleware(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $this->assertEquals(120, $options['timeout']);
                $this->assertTrue($options['debug']);
                $this->assertEquals('shanty=yes&sing=yes', $request->getUri()->getQuery());
                $this->assertEquals('South-Australia', $request->getHeaderLine('X-Bound-For'));
                $this->assertEquals('valOne, valTwo', $request->getHeaderLine('X-Fancy'));

                // Return fulfilled promise to fake response

                return new FulfilledPromise(new Response());
            };
        });

        $connector->send($request);
    }

    public function testTheGuzzleSenderHasTheDefaultHandlerStackConfiguredByDefault()
    {
        $connector = new TestConnector();
        $sender = $connector->sender();

        $this->assertInstanceOf(GuzzleSender::class, $sender);

        $handlerStack = $sender->getHandlerStack();

        // The HandlerStack::create() loads important default middleware

        $this->assertEquals(HandlerStack::create(), $handlerStack);
    }

    public function testTheGuzzleSenderHasDefaultOptionsConfigured()
    {
        $connector = new TestConnector();
        $sender = $connector->sender();

        $this->assertInstanceOf(GuzzleSender::class, $sender);

        $client = $sender->getGuzzleClient();

        $freshClient = new Client([
            'connect_timeout' => 10,
            'timeout' => 30,
            'http_errors' => true,
            'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
        ]);

        $this->assertEquals($freshClient->getConfig(), $client->getConfig());
    }

    public function testYouCanSetACustomHandlerStackOnTheGuzzleSender()
    {
        $connector = new TestConnector();
        $sender = $connector->sender();

        $handlerStack = new HandlerStack(choose_handler());

        $sender->setHandlerStack($handlerStack);

        $this->assertSame($handlerStack, $sender->getHandlerStack());
    }
}
