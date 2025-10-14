<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class RequestSelectionConnector extends Connector
{
    use AcceptsJson;

    /**
     * @var string|null
     */
    public $apiKey;

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
     * @param string|null $apiKey
     */
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }
}
