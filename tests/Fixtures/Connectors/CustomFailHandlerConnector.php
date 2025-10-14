<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class CustomFailHandlerConnector extends Connector
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
     * Determine if the request has failed
     *
     * @return bool|null
     */
    public function hasRequestFailed(Response $response)
    {
        return strpos($response->body(), 'Error:') !== false;
    }
}
