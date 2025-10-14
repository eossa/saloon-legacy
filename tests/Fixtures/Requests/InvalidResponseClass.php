<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Responses\UserResponseNoExtendSaloonResponse;

class InvalidResponseClass extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * Define the custom response
     *
     * @var string|null
     */
    protected $response = UserResponseNoExtendSaloonResponse::class;

    /**
     * The connector.
     *
     * @var string
     */
    protected $connector = TestConnector::class;

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
