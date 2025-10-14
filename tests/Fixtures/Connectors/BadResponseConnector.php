<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class BadResponseConnector extends Connector
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
     * Check if we should throw an exception
     *
     * @return bool
     */
    public function shouldThrowRequestException(Response $response)
    {
        return strpos($response->body(), 'Error:') !== false;
    }
}
