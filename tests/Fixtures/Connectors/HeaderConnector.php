<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class HeaderConnector extends Connector
{
    use AcceptsJson;

    /**
     * @return string
     */
    public function resolveBaseUrl()
    {
        return apiUrl();
    }

    /**
     * @return string[]
     */
    public function defaultHeaders()
    {
        return [
            'X-Connector-Header' => 'Sam',
        ];
    }

    /**
     * @return array
     */
    public function defaultConfig()
    {
        return [
            'http_errors' => false,
        ];
    }
}
