<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;

class NoConfigClientCredentialsConnector extends Connector
{
    use ClientCredentialsGrant;

    /**
     * Define the base URL.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return 'https://oauth.saloon.dev';
    }
}
