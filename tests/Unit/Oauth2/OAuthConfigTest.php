<?php

namespace Saloon\Tests\Unit\Oauth2;

use PHPUnit\Framework\TestCase;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Exceptions\OAuthConfigValidationException;

class OAuthConfigTest extends TestCase
{
    public function testAllDefaultPropertiesAreCorrectAndAllGettersAndSettersWorkProperly()
    {
        $config = new OAuthConfig();

        $this->assertEquals('', $config->getClientId());
        $this->assertEquals('', $config->getClientSecret());
        $this->assertEquals('', $config->getRedirectUri());
        $this->assertEquals('authorize', $config->getAuthorizeEndpoint());
        $this->assertEquals('token', $config->getTokenEndpoint());
        $this->assertEquals('user', $config->getUserEndpoint());
        $this->assertEquals(array(), $config->getDefaultScopes());

        $clientId = 'client-id';
        $clientSecret = 'client-secret';
        $redirectUri = 'https://my-app.saloon.dev/auth/callback';
        $authorizeEndpoint = 'auth/authorize';
        $tokenEndpoint = 'auth/token';
        $userEndpoint = 'auth/user';
        $defaultScopes = array('profile');

        $this->assertEquals($config, $config->setClientId($clientId));
        $this->assertEquals($config, $config->setClientSecret($clientSecret));
        $this->assertEquals($config, $config->setRedirectUri($redirectUri));
        $this->assertEquals($config, $config->setAuthorizeEndpoint($authorizeEndpoint));
        $this->assertEquals($config, $config->setTokenEndpoint($tokenEndpoint));
        $this->assertEquals($config, $config->setUserEndpoint($userEndpoint));
        $this->assertEquals($config, $config->setDefaultScopes($defaultScopes));

        $this->assertEquals($clientId, $config->getClientId());
        $this->assertEquals($clientSecret, $config->getClientSecret());
        $this->assertEquals($redirectUri, $config->getRedirectUri());
        $this->assertEquals($authorizeEndpoint, $config->getAuthorizeEndpoint());
        $this->assertEquals($tokenEndpoint, $config->getTokenEndpoint());
        $this->assertEquals($userEndpoint, $config->getUserEndpoint());
        $this->assertEquals($defaultScopes, $config->getDefaultScopes());
    }

    public function testMakeMethodCreatesAnInstanceOfOAuthConfig()
    {
        $this->assertInstanceOf(OAuthConfig::class, OAuthConfig::make());
    }

    public function testItWillThrowAnExceptionIfYouDoNotSpecifyTheClientId()
    {
        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client ID is empty or has not been provided.');

        $config = new OAuthConfig();
        $config->validate();
    }

    public function testItWillThrowAnExceptionIfYouDoNotSpecifyTheClientSecret()
    {
        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Client Secret is empty or has not been provided.');

        $config = new OAuthConfig();
        $config->setClientId('client-id');

        $config->validate();
    }

    public function testItWillThrowAnExceptionIfYouDoNotSpecifyTheRedirectUri()
    {
        $this->expectException(OAuthConfigValidationException::class);
        $this->expectExceptionMessage('The Redirect URI is empty or has not been provided.');

        $config = new OAuthConfig();

        $config->setClientId('client-id')
            ->setClientSecret('client-secret');

        $config->validate();
    }
}
