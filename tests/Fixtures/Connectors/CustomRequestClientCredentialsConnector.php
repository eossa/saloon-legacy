<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Tests\Fixtures\Requests\OAuth\CustomClientCredentialsAccessTokenRequest;

class CustomRequestClientCredentialsConnector extends ClientCredentialsConnector
{
    /**
     * Resolve the access token request
     *
     * @param OAuthConfig $oauthConfig
     * @param array $scopes
     * @param string $scopeSeparator
     *
     * @return Request
     */
    protected function resolveAccessTokenRequest(OAuthConfig $oauthConfig, array $scopes = [], $scopeSeparator = ' ')
    {
        return new CustomClientCredentialsAccessTokenRequest($oauthConfig, $scopes, $scopeSeparator);
    }
}
