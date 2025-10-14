<?php

namespace Saloon\Traits\OAuth2;

use Saloon\Http\Request;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\OAuth2\GetClientCredentialsTokenBasicAuthRequest;

trait ClientCredentialsBasicAuthGrant
{
    use ClientCredentialsGrant;

    /**
     * Resolve the access token request
     *
     * @param string $scopeSeparator
     *
     * @return Request
     */
    protected function resolveAccessTokenRequest(OAuthConfig $oauthConfig, array $scopes = [], $scopeSeparator = ' ')
    {
        return new GetClientCredentialsTokenBasicAuthRequest($oauthConfig, $scopes, $scopeSeparator);
    }
}
