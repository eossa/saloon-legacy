<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;

class NoConfigAuthCodeConnector extends Connector
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
}
