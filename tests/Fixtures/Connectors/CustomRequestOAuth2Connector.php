<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomOAuthUserRequest;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomAccessTokenRequest;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomRefreshTokenRequest;

class CustomRequestOAuth2Connector extends Connector
{
    use AuthorizationCodeGrant;

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
            ->setRedirectUri('https://my-app.saloon.dev/auth/callback');
    }

    /**
     * Resolve the access token request
     *
     * @param string $code
     *
     * @return Request
     */
    protected function resolveAccessTokenRequest($code, OAuthConfig $oauthConfig)
    {
        return new CustomAccessTokenRequest($code, $oauthConfig);
    }

    /**
     * Resolve the refresh token request
     *
     * @param string $refreshToken
     *
     * @return Request
     */
    protected function resolveRefreshTokenRequest(OAuthConfig $oauthConfig, $refreshToken)
    {
        return new CustomRefreshTokenRequest($oauthConfig, $refreshToken);
    }

    /**
     * Resolve the user request
     *
     * @return Request
     */
    protected function resolveUserRequest(OAuthConfig $oauthConfig)
    {
        return new CustomOAuthUserRequest($oauthConfig);
    }
}
