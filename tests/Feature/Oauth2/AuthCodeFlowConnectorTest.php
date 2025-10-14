<?php

namespace Saloon\Tests\Feature\Oauth2;

use DateTimeImmutable;
use InvalidArgumentException;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Tests\Helpers\Date;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\OAuth2\GetUserRequest;
use Saloon\Exceptions\InvalidStateException;
use Saloon\Http\OAuth2\GetAccessTokenRequest;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\OAuth2\GetRefreshTokenRequest;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Tests\Fixtures\Connectors\OAuth2Connector;
use Saloon\Tests\Fixtures\Connectors\NoConfigAuthCodeConnector;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomOAuthUserRequest;
use Saloon\Tests\Fixtures\Authenticators\CustomOAuthAuthenticator;
use Saloon\Tests\Fixtures\Connectors\CustomRequestOAuth2Connector;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomAccessTokenRequest;
use Saloon\Tests\Fixtures\Connectors\CustomResponseOAuth2Connector;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomRefreshTokenRequest;
use PHPUnit\Framework\TestCase;

class AuthCodeFlowConnectorTest extends TestCase
{
    public function testYouCanGetTheRedirectUrlFromAConnector()
    {
        $connector = new OAuth2Connector();

        $this->assertNull($connector->getState());

        $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], 'my-state');

        $state = $connector->getState();

        $this->assertEquals('my-state', $state);

        $this->assertEquals(
            'https://oauth.saloon.dev/authorize?response_type=code&scope=scope-1%20scope-2&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Fauth%2Fcallback&state=my-state',
            $url
        );
    }

    public function testYouCanProvideDefaultScopesThatWillBeAppliedToEveryAuthorizationUrl()
    {
        $connector = new OAuth2Connector();

        $connector->oauthConfig()->setDefaultScopes(['scope-3']);

        $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], 'my-state');

        $this->assertEquals(
            'https://oauth.saloon.dev/authorize?response_type=code&scope=scope-3%20scope-1%20scope-2&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Fauth%2Fcallback&state=my-state',
            $url
        );
    }

    public function testYouCanGetAuthorizationUrlWithoutSettingValidScopes()
    {
        $connector = new OAuth2Connector();

        $connector->oauthConfig()->setDefaultScopes(['', null]);

        $url = $connector->getAuthorizationUrl([], 'my-state');

        $this->assertEquals(
            'https://oauth.saloon.dev/authorize?response_type=code&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Fauth%2Fcallback&state=my-state',
            $url
        );
    }

    public function testDefaultStateIsGeneratedAutomaticallyWithEveryAuthorizationUrlIfStateIsNotDefined()
    {
        $connector = new OAuth2Connector();

        $connector->oauthConfig()->setDefaultScopes(['scope-3']);

        $this->assertNull($connector->getState());

        $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2']);
        $state = $connector->getState();

        $this->assertTrue(is_string($state));

        $this->assertTrue(substr($url, -strlen($state)) === $state);
    }

    public function testAdditionalQueryParametersCanBeAddedPassedToTheAuthorizationUrl()
    {
        $connector = new OAuth2Connector();

        // In PHP 5.6, we need to use traditional parameter passing instead of named parameters
        $url = $connector->getAuthorizationUrl(
            ['scope-1', 'scope-2'],
            'my-state',
            ' ',
            ['another-param' => 'another-value', 'yee' => 'haw']
        );

        // Test that the additional parameters are included in the URL
        $this->assertContains('another-param=another-value', $url);
        $this->assertContains('yee=haw', $url);
        $this->assertEquals(
            'https://oauth.saloon.dev/authorize?response_type=code&scope=scope-1%20scope-2&client_id=client-id&redirect_uri=https%3A%2F%2Fmy-app.saloon.dev%2Fauth%2Fcallback&state=my-state&another-param=another-value&yee=haw',
            $url
        );
    }

    public function testYouCanRequestATokenFromAConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600], 200),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken('code');

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);
        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertEquals('refresh', $authenticator->getRefreshToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $authenticator->getExpiresAt());
    }

    public function testYouCanTapIntoTheAccessTokenRequestAndModifyIt()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600], 200),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken('code', null, null, false, function (Request $request) {
            $request->query()->add('yee', 'haw');
        });

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);
        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertEquals('refresh', $authenticator->getRefreshToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $authenticator->getExpiresAt());

        $mockClient->assertSentCount(1);

        $this->assertEquals(['yee' => 'haw'], $mockClient->getLastPendingRequest()->query()->all());
    }

    public function testYouCanRequestTheOriginalResponseInsteadOfTheAuthenticatorOnTheCreateTokensMethod()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $response = $connector->getAccessToken('code', null, null, true);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600], $response->json());
    }

    public function testItWillThrowAnExceptionIfStateIsInvalid()
    {
        $connector = new OAuth2Connector();

        $state = 'secret';
        $url = $connector->getAuthorizationUrl(['scope-1', 'scope-2'], $state);

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('Invalid state.');

        $connector->getAccessToken('code', 'invalid', $state);
    }

    public function testYouCanRefreshATokenFromAConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = new AccessTokenAuthenticator('access', 'refresh', Date::now()->addSeconds(3600)->toDateTime());

        $newAuthenticator = $connector->refreshAccessToken($authenticator);

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $newAuthenticator);
        $this->assertEquals('access-new', $newAuthenticator->getAccessToken());
        $this->assertEquals('refresh-new', $newAuthenticator->getRefreshToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $newAuthenticator->getExpiresAt());
    }

    public function testYouCanTapIntoTheRefreshTokenRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = new AccessTokenAuthenticator('access', 'refresh', Date::now()->addSeconds(3600)->toDateTime());

        $newAuthenticator = $connector->refreshAccessToken($authenticator, false, function (Request $request) {
            $request->query()->add('yee', 'haw');
        });

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $newAuthenticator);
        $this->assertEquals('access-new', $newAuthenticator->getAccessToken());
        $this->assertEquals('refresh-new', $newAuthenticator->getRefreshToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $newAuthenticator->getExpiresAt());

        $mockClient->assertSentCount(1);

        $this->assertEquals(['yee' => 'haw'], $mockClient->getLastPendingRequest()->query()->all());
    }

    public function testTheRefreshAccessTokenMethodThrowsAnExceptionIfYouProvideItAnAuthenticatorThatIsNotRefreshable()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = new AccessTokenAuthenticator('access', null, Date::now()->addSeconds(3600)->toDateTime());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The provided OAuthAuthenticator does not contain a refresh token.');

        $connector->refreshAccessToken($authenticator);
    }

    public function testYouCanRequestTheOriginalResponseInsteadOfTheAuthenticatorOnTheRefreshTokensMethod()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = new AccessTokenAuthenticator('access', 'refresh', Date::now()->addSeconds(3600)->toDateTime());

        $response = $connector->refreshAccessToken($authenticator, true);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600], $response->json());
    }

    public function testYouCanGetTheUserFromAnOauthConnector()
    {
        $mockClient = new MockClient([
            MockResponse::make(['user' => 'Sam']),
        ]);

        $connector = new OAuth2Connector();
        $connector->withMockClient($mockClient);

        $accessToken = new AccessTokenAuthenticator('access', 'refresh', Date::now()->addSeconds(3600)->toDateTime());

        $response = $connector->getUser($accessToken);

        $this->assertInstanceOf(Response::class, $response);

        $pendingRequest = $response->getPendingRequest();

        $this->assertEquals([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer access',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $pendingRequest->headers()->all());
    }

    public function testYouCanTapIntoTheTheUserRequest()
    {
        $mockClient = new MockClient([
            MockResponse::make(['user' => 'Sam']),
        ]);

        $connector = new OAuth2Connector();
        $connector->withMockClient($mockClient);

        $accessToken = new AccessTokenAuthenticator('access', 'refresh', Date::now()->addSeconds(3600)->toDateTime());

        $response = $connector->getUser($accessToken, function (Request $request) {
            $request->query()->add('yee', 'haw');
        });

        $this->assertInstanceOf(Response::class, $response);

        $pendingRequest = $response->getPendingRequest();

        $this->assertEquals(['yee' => 'haw'], $pendingRequest->query()->all());

        $this->assertEquals([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer access',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $pendingRequest->headers()->all());
    }

    public function testYouCanCustomizeTheOauthAuthenticator()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
        ]);

        $customConnector = new CustomResponseOAuth2Connector('Howdy!');
        $customConnector->withMockClient($mockClient);

        $authenticator = $customConnector->getAccessToken('code');

        $this->assertInstanceOf(CustomOAuthAuthenticator::class, $authenticator);
        $this->assertEquals('Howdy!', $authenticator->getGreeting());
    }

    public function testYouCanRegisterAGlobalRequestModifierThatIsCalledOnEveryStepOfTheOAuth2Process()
    {
        $mockClient = new MockClient([
            GetAccessTokenRequest::class => MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600], 200),
            GetRefreshTokenRequest::class => MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
            GetUserRequest::class => MockResponse::make(['user' => 'Sam']),
        ]);

        $connector = new OAuth2Connector();
        $requests = [];

        $connector->oauthConfig()->setRequestModifier(function (Request $request) use (&$requests) {
            $requests[] = get_class($request);

            if ($request instanceof GetAccessTokenRequest) {
                $request->query()->add('request', 'access');
            } elseif ($request instanceof GetRefreshTokenRequest) {
                $request->query()->add('request', 'refresh');
            } elseif ($request instanceof GetUserRequest) {
                $request->query()->add('request', 'user');
            }
        });

        $connector->withMockClient($mockClient);

        $authenticator = $connector->getAccessToken('code');

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $authenticator);
        $this->assertEquals('access', $authenticator->getAccessToken());
        $this->assertEquals('refresh', $authenticator->getRefreshToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $authenticator->getExpiresAt());
        $this->assertEquals(['request' => 'access'], $mockClient->getLastPendingRequest()->query()->all());

        $newAuthenticator = $connector->refreshAccessToken($authenticator);

        $this->assertInstanceOf(AccessTokenAuthenticator::class, $newAuthenticator);
        $this->assertEquals('access-new', $newAuthenticator->getAccessToken());
        $this->assertEquals('refresh-new', $newAuthenticator->getRefreshToken());
        $this->assertInstanceOf(DateTimeImmutable::class, $newAuthenticator->getExpiresAt());
        $this->assertEquals(['request' => 'refresh'], $mockClient->getLastPendingRequest()->query()->all());

        $response = $connector->getUser($newAuthenticator);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(['request' => 'user'], $mockClient->getLastPendingRequest()->query()->all());

        $pendingRequest = $response->getPendingRequest();

        $this->assertEquals([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer access-new',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $pendingRequest->headers()->all());

        $this->assertEquals([
            GetAccessTokenRequest::class,
            GetRefreshTokenRequest::class,
            GetUserRequest::class,
        ], $requests);
    }

    public function testIfYouAttemptToUseTheAuthorizationCodeFlowWithoutAClientIdItWillThrowAnException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new NoConfigAuthCodeConnector();
        $connector->withMockClient($mockClient);

        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

        $connector->getAccessToken('code');
    }

    public function testIfYouAttemptToUseTheAuthorizationCodeFlowWithoutASecretItWillThrowAnException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new NoConfigAuthCodeConnector();
        $connector->withMockClient($mockClient);

        $connector->oauthConfig()->setClientId('hello');

        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client Secret is empty or has not been provided.');

        $connector->getAccessToken('code');
    }

    public function testIfYouAttemptToUseTheAuthorizationCodeFlowWithoutARedirectUriItWillThrowAnException()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access', 'expires_in' => 3600], 200),
        ]);

        $connector = new NoConfigAuthCodeConnector();
        $connector->withMockClient($mockClient);

        $connector->oauthConfig()->setClientId('hello');
        $connector->oauthConfig()->setClientSecret('secret');

        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Redirect URI is empty or has not been provided.');

        $connector->getAccessToken('code');
    }

    public function testOnTheConnectorYouCanOverwriteAllTheRequestClasses()
    {
        $mockClient = new MockClient([
            CustomAccessTokenRequest::class => MockResponse::make(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 3600], 200),
            CustomRefreshTokenRequest::class => MockResponse::make(['access_token' => 'access-new', 'refresh_token' => 'refresh-new', 'expires_in' => 3600]),
            CustomOAuthUserRequest::class => MockResponse::make(['user' => 'Sam']),
        ]);

        $connector = new CustomRequestOAuth2Connector();
        $connector->withMockClient($mockClient);

        $accessTokenResponse = $connector->getAccessToken('code', null, null, true);

        $this->assertInstanceOf(CustomAccessTokenRequest::class, $accessTokenResponse->getRequest());

        $refreshTokenResponse = $connector->refreshAccessToken('howdy', true);

        $this->assertInstanceOf(CustomRefreshTokenRequest::class, $refreshTokenResponse->getRequest());

        $userResponse = $connector->getUser(new AccessTokenAuthenticator('howdy', 'partner'));

        $this->assertInstanceOf(CustomOAuthUserRequest::class, $userResponse->getRequest());
    }
}
