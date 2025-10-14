<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\HasTimeout;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class TimeoutRequest extends Request
{
    use HasTimeout;

    /**
     * @var int
     */
    protected $connectTimeout = 1;

    /**
     * @var int
     */
    protected $requestTimeout = 2;

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
