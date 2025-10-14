<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\CustomBaseUrlConnector;

class CustomEndpointRequest extends Request
{
    /**
     * Connector
     *
     * @var string
     */
    protected $connector = CustomBaseUrlConnector::class;

    /**
     * Endpoint
     *
     * @var string
     */
    protected $endpoint = '';

    /**
     * Method
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set an endpoint
     *
     * @param string $endpoint
     *
     * @return CustomEndpointRequest
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }
}
