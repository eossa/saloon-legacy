<?php

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\HeaderConnector;

class HeaderRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected $method = Method::GET;

    /**
     * The connector.
     *
     * @var string
     */
    protected $connector = HeaderConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint()
    {
        return '/user';
    }

    /**
     * @return string[]
     */
    protected function defaultHeaders()
    {
        return [
            'X-Custom-Header' => 'Howdy',
        ];
    }

    /**
     * @return int[]
     */
    protected function defaultConfig()
    {
        return [
            'timeout' => 5,
        ];
    }
}
