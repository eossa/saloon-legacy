<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\HasTimeout;
use Saloon\Traits\Plugins\AcceptsJson;

class TimeoutConnector extends Connector
{
    use AcceptsJson;
    use HasTimeout;

    /**
     * @var int
     */
    protected $connectTimeout = 10;

    /**
     * @var int
     */
    protected $requestTimeout = 5;

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
}
