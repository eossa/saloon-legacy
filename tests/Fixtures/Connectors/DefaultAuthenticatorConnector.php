<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\TokenAuthenticator;

class DefaultAuthenticatorConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    public function defaultHeaders()
    {
        return [];
    }

    /**
     * Provide default authentication.
     *
     * @return Authenticator|null
     */
    public function defaultAuth()
    {
        return new TokenAuthenticator('yee-haw-connector');
    }
}
