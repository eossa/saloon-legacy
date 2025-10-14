<?php

namespace Saloon\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\MissingAuthenticatorException;
use Saloon\Tests\Fixtures\Requests\RequiresAuthRequest;
use Saloon\Tests\Fixtures\Authenticators\PizzaAuthenticator;
use Saloon\Tests\Fixtures\Requests\BootAuthenticatorRequest;
use Saloon\Tests\Fixtures\Requests\AuthenticatorPluginRequest;
use Saloon\Tests\Fixtures\Requests\DefaultAuthenticatorRequest;
use Saloon\Tests\Fixtures\Connectors\DefaultAuthenticatorConnector;
use Saloon\Tests\Fixtures\Requests\DefaultPizzaAuthenticatorRequest;

class AuthenticatorTest extends TestCase
{
    public function testYouCanAddAnAuthenticatorToARequestAndItWillBeApplied()
    {
        $request = new DefaultAuthenticatorRequest();
        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertEquals('Bearer yee-haw-request', $pendingRequest->headers()->get('Authorization'));
    }

    public function testYouCanProvideADefaultAuthenticatorOnTheConnector()
    {
        $request = new UserRequest();
        $connector = new DefaultAuthenticatorConnector();

        $pendingRequest = $connector->createPendingRequest($request);

        $this->assertEquals('Bearer yee-haw-connector', $pendingRequest->headers()->get('Authorization'));
    }

    public function testYouCanProvideADefaultAuthenticatorOnTheRequestAndItTakesPriorityOverTheConnector()
    {
        $request = new DefaultAuthenticatorRequest();
        $connector = new DefaultAuthenticatorConnector();

        $pendingRequest = $connector->createPendingRequest($request);

        $this->assertEquals('Bearer yee-haw-request', $pendingRequest->headers()->get('Authorization'));
    }

    public function testYouCanProvideAnAuthenticatorOnTheFlyAndItWillTakePriorityOverAllDefaults()
    {
        $request = new DefaultAuthenticatorRequest();
        $connector = new DefaultAuthenticatorConnector();

        $request->withTokenAuth('yee-haw-on-the-fly', 'PewPew');

        $pendingRequest = $connector->createPendingRequest($request);

        $this->assertEquals('PewPew yee-haw-on-the-fly', $pendingRequest->headers()->get('Authorization'));
    }

    public function testTheRequiresAuthTraitWillThrowAnExceptionIfAnAuthenticatorIsNotFound()
    {
        $mockClient = new MockClient([
            MockResponse::make(),
        ]);

        $this->expectException(MissingAuthenticatorException::class);
        $this->expectExceptionMessage('The "Saloon\Tests\Fixtures\Requests\RequiresAuthRequest" request requires authentication.');

        $request = new RequiresAuthRequest();

        connector()->send($request, $mockClient);
    }

    public function testYouCanUseYourOwnAuthenticators()
    {
        $request = new UserRequest();
        $request->authenticate(new PizzaAuthenticator('Margherita', 'San Pellegrino'));

        $pendingRequest = connector()->createPendingRequest($request);

        $headers = $pendingRequest->headers()->all();

        $this->assertEquals('Margherita', $headers['X-Pizza']);
        $this->assertEquals('San Pellegrino', $headers['X-Drink']);
        $this->assertTrue($pendingRequest->config()->get('debug'));
    }

    public function testYouCanUseYourOwnAuthenticatorsAsDefault()
    {
        $request = new DefaultPizzaAuthenticatorRequest();

        $pendingRequest = connector()->createPendingRequest($request);

        $headers = $pendingRequest->headers()->all();

        $this->assertEquals('BBQ Chicken', $headers['X-Pizza']);
        $this->assertEquals('Lemonade', $headers['X-Drink']);
        $this->assertTrue($pendingRequest->config()->get('debug'));
    }

    public function testYouCanCustomiseTheAuthenticatorInsideOfTheBootMethod()
    {
        $request = new BootAuthenticatorRequest();

        $this->assertNull($request->getAuthenticator());

        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertEquals(new TokenAuthenticator('howdy-partner'), $pendingRequest->getAuthenticator());
        $this->assertEquals('Bearer howdy-partner', $pendingRequest->headers()->get('Authorization'));
    }

    public function testYouCanCustomiseTheAuthenticatorInsideOfPlugins()
    {
        $request = new AuthenticatorPluginRequest();

        $this->assertNull($request->getAuthenticator());

        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertEquals(new TokenAuthenticator('plugin-auth'), $pendingRequest->getAuthenticator());
        $this->assertEquals('Bearer plugin-auth', $pendingRequest->headers()->get('Authorization'));
    }

    public function testYouCanCustomiseTheAuthenticatorInsideOfAMiddlewarePipeline()
    {
        $request = new UserRequest();

        $this->assertNull($request->getAuthenticator());

        $request->middleware()
            ->onRequest(function (PendingRequest $pendingRequest) {
                $pendingRequest->withTokenAuth('ooh-this-is-cool');
            });

        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertEquals(new TokenAuthenticator('ooh-this-is-cool'), $pendingRequest->getAuthenticator());
        $this->assertEquals('Bearer ooh-this-is-cool', $pendingRequest->headers()->get('Authorization'));
    }

    public function testYouCanAddAnAuthenticatorInsideOfRequestMiddleware()
    {
        $request = new UserRequest();

        $request->middleware()->onRequest(function (PendingRequest $pendingRequest) {
            return $pendingRequest->withTokenAuth('yee-haw-request');
        });

        $pendingRequest = connector()->createPendingRequest($request);

        $this->assertEquals('Bearer yee-haw-request', $pendingRequest->headers()->get('Authorization'));
    }

    public function testIfYouUseTheAuthenticateMethodOnAFullyConstructedPendingRequestItWillAuthenticateRightAway()
    {
        $connector = new TestConnector();
        $pendingRequest = $connector->createPendingRequest(new UserRequest());

        $this->assertEquals([
            'Accept' => 'application/json',
        ], $pendingRequest->headers()->all());

        $pendingRequest->authenticate(new TokenAuthenticator('yee-haw-request'));

        $this->assertEquals([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer yee-haw-request',
        ], $pendingRequest->headers()->all());
    }
}
