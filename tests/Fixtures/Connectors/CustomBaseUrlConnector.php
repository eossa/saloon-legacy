<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;

class CustomBaseUrlConnector extends Connector
{
    /**
     * Base URL
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    public function resolveBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set a base URL
     *
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }
}
