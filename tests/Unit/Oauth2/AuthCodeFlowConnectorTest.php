<?php

namespace Saloon\Tests\Unit\Oauth2;

use PHPUnit\Framework\TestCase;
use Saloon\Tests\Helpers\Date;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Exceptions\OAuthConfigValidationException;
use Saloon\Tests\Fixtures\Connectors\OAuth2Connector;

class AuthCodeFlowConnectorTest extends TestCase
{
    public function testTheOauth2ConfigClassCanBeConfiguredProperly()
    {
        $connector = new OAuth2Connector();

        $config = $connector->oauthConfig();

        $this->assertInstanceOf(OAuthConfig::class, $config);
        $this->assertEquals('client-id', $config->getClientId());
        $this->assertEquals('client-secret', $config->getClientSecret());
        $this->assertEquals('https://my-app.saloon.dev/auth/callback', $config->getRedirectUri());
    }

    public function testTheOauthConfigIsValidatedWhenGeneratingAnAuthorizationUrl()
    {
        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

        $connector = new OAuth2Connector();
        $connector->oauthConfig()->setClientId('');

        $connector->getAuthorizationUrl();
    }

    public function testTheOauthConfigIsValidatedWhenCreatingAccessTokens()
    {
        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

        $connector = new OAuth2Connector();
        $connector->oauthConfig()->setClientId('');

        $connector->getAccessToken('code');
    }

    public function testTheOauthConfigIsValidatedWhenRefreshingAccessTokens()
    {
        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

        $connector = new OAuth2Connector();
        $connector->oauthConfig()->setClientId('');

        $connector->refreshAccessToken('');
    }

    public function testTheOldRefreshTokenIsCarriedOverIfAResponseDoesNotIncludeANewRefreshToken()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $authenticator = new AccessTokenAuthenticator('access', 'refresh-old', Date::now()->addSeconds(3600)->toDateTime());

        $newAuthenticator = $connector->refreshAccessToken($authenticator);

        $this->assertEquals('refresh-old', $newAuthenticator->getRefreshToken());
    }

    public function testTheOldRefreshTokenIsCarriedOverIfAResponseDoesNotIncludeANewRefreshTokenAndTheRefreshIsAString()
    {
        $mockClient = new MockClient([
            MockResponse::make(['access_token' => 'access-new', 'expires_in' => 3600]),
        ]);

        $connector = new OAuth2Connector();

        $connector->withMockClient($mockClient);

        $newAuthenticator = $connector->refreshAccessToken('refresh-old');

        $this->assertEquals('refresh-old', $newAuthenticator->getRefreshToken());
    }
}
