<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\ClientCredentialsBasicAuthGrant;

class ClientCredentialsBasicAuthConnector extends Connector
{
    use ClientCredentialsBasicAuthGrant;

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
            ->setClientSecret('client-secret');
    }
}
