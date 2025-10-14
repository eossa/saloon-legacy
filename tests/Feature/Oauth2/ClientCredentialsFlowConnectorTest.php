<?php

namespace Saloon\Tests\Feature\Oauth2;

use DateTimeImmutable;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Tests\Fixtures\Connectors\ClientCredentialsConnector;
use Saloon\Tests\Fixtures\Connectors\NoConfigClientCredentialsConnector;
use Saloon\Tests\Fixtures\Connectors\ClientCredentialsBasicAuthConnector;
use Saloon\Tests\Fixtures\Connectors\CustomRequestClientCredentialsConnector;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomClientCredentialsAccessTokenRequest;
use PHPUnit\Framework\TestCase;

class ClientCredentialsFlowConnectorTest extends TestCase
{
    public function testYouCanGetTheAuthenticatorFromTheConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken();

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);
        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertNull($authenticator->getRefreshToken());
        $this->assertFalse($authenticator->isRefreshable());
        $this->assertInstanceOf(DateTimeImmutable::class, $authenticator->getExpiresAt());

        $mockClient->assertSentCount(1);

        $this->assertEquals([
            'grant_type' => 'client_credentials',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'scope' => '',
        ], $mockClient->getLastPendingRequest()->body()->all());
    }

    public function testYouCanGetTheResponseInsteadOfTheAuthenticator()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $response = $connector->getAccessToken([], ' ', true);

        $this->assertInstanceOf(Response::class, $response);

        $this->assertEquals([
            'access_token' => 'access',
            'expires_in' => 3600,
        ], $response->json());
    }

    public function testYouCanTapIntoTheTokenRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken([], ' ', false, function (Request $request) {
            $request->query()->add('yee', 'haw');
        });

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);
        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertNull($authenticator->getRefreshToken());
        $this->assertFalse($authenticator->isRefreshable());
        $this->assertInstanceOf(DateTimeImmutable::class, $authenticator->getExpiresAt());

        $mockClient->assertSentCount(1);

        $this->assertEquals(['yee' => 'haw'], $mockClient->getLastPendingRequest()->query()->all());
    }

    public function testYouCanSendScopesWithTheTokenRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken(['offline_access', 'clients', 'billing']);

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);

        $mockClient->assertSentCount(1);

        $this->assertEquals([
            'grant_type' => 'client_credentials',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'scope' => 'offline_access clients billing',
        ], $mockClient->getLastPendingRequest()->body()->all());
    }

    public function testDefaultScopesOnTheOauthConfigWillBeMergedInWithTheScopesOnTheTokenRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $connector->oauthConfig()->setDefaultScopes([
            'compliance',
        ]);

        $authenticator = $connector->getAccessToken(['offline_access', 'clients', 'billing']);

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);

        $mockClient->assertSentCount(1);

        $this->assertEquals([
            'grant_type' => 'client_credentials',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'scope' => 'compliance offline_access clients billing',
        ], $mockClient->getLastPendingRequest()->body()->all());
    }

    public function testTheScopeSeparatorCanBeCustomised()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $connector->oauthConfig()->setDefaultScopes([
            'compliance',
        ]);

        $authenticator = $connector->getAccessToken(['offline_access', 'clients', 'billing'], '+');

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);

        $mockClient->assertSentCount(1);

        $this->assertEquals([
            'grant_type' => 'client_credentials',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'scope' => 'compliance+offline_access+clients+billing',
        ], $mockClient->getLastPendingRequest()->body()->all());
    }

    public function testIfYouAttemptToUseTheClientCredentialsFlowWithoutAClientIdItWillThrowAnException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new NoConfigClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

        $connector->getAccessToken();
    }

    public function testIfYouAttemptToUseTheClientCredentialsFlowWithoutASecretItWillThrowAnException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new NoConfigClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $connector->oauthConfig()->setClientId('hello');

        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client Secret is empty or has not been provided.');

        $connector->getAccessToken();
    }

    public function testOnTheConnectorYouCanOverwriteTheGetAccessTokenRequest()
    {
        $mockClient = new MockClient([
            CustomClientCredentialsAccessTokenRequest::class => MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new CustomRequestClientCredentialsConnector();
        $connector->withMockClient($mockClient);

        $accessTokenResponse = $connector->getAccessToken([], ' ', true);

        $this->assertInstanceOf(CustomClientCredentialsAccessTokenRequest::class, $accessTokenResponse->getRequest());
    }

    public function testTheClientCredentialsGrantCanUseBasicAuth()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new ClientCredentialsBasicAuthConnector();
        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken();

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);
        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertNull($authenticator->getRefreshToken());
        $this->assertFalse($authenticator->isRefreshable());
        $this->assertInstanceOf(DateTimeImmutable::class, $authenticator->getExpiresAt());

        $mockClient->assertSentCount(1);

        $this->assertEquals([
            'grant_type' => 'client_credentials',
            'scope' => '',
        ], $mockClient->getLastPendingRequest()->body()->all());

        $this->assertEquals(
            'Basic ' . base64_encode('client-id:client-secret'),
            $mockClient->getLastPendingRequest()->headers()->get('Authorization')
        );
    }
}
