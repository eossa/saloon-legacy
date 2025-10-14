<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\WithBootConnector;

class UserRequestWithBootConnector extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected $method = Method::GET;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected $connector = WithBootConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }
}
