<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultEndpointRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected $method = Method::POST;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected $connector = TestConnector::class;


    /**
     * @return string
     */
    public function resolveEndpoint()
    {
        return '';
    }
}
