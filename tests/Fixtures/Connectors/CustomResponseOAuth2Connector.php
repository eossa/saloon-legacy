<?php

namespace Saloon\Tests\Fixtures\Connectors;

use DateTimeImmutable;
use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Contracts\OAuthAuthenticator;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Tests\Fixtures\Authenticators\CustomOAuthAuthenticator;

class CustomResponseOAuth2Connector extends Connector
{
    use AuthorizationCodeGrant;

    /**
     * @var string
     */
    protected $greeting;


    /**
     * @param string $greeting
     */
    public function __construct($greeting)
    {
        $this->greeting = $greeting;
    }

    /**
     * Define the base URL.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return 'https://oauth.saloon.dev';
    }

    /**
     * Define default Oauth config.
     *
     * @return OAuthConfig
     */
    protected function defaultOauthConfig()
    {
        return OAuthConfig::make()
            ->setClientId('client-id')
            ->setClientSecret('client-secret')
            ->setRedirectUri('https://my-app.saloon.dev/oauth/redirect');
    }

    /**
     * Create the OAuth authenticator
     *
     * @param string $accessToken
     * @param string|null $refreshToken
     * @param DateTimeImmutable|null $expiresAt
     *
     * @return OAuthAuthenticator
     */
    protected function createOAuthAuthenticator($accessToken, $refreshToken = null, DateTimeImmutable $expiresAt = null)
    {
        return new CustomOAuthAuthenticator($accessToken, $this->greeting,  $refreshToken, $expiresAt);
    }
}
